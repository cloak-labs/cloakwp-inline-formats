<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Plugin;

use CloakWP\InlineFormats\Core\Config;

/**
 * Registers and enqueues block-editor JS via plugin_dir_url().
 */
final class Assets
{
  public const SCRIPT_HANDLE = 'inline-formats-editor';

  private bool $localized = false;

  public function __construct(
    private readonly Config $config,
    private readonly string $pluginFile,
  ) {
  }

  public function register(): void
  {
    add_action('enqueue_block_editor_assets', [$this, 'enqueue'], 20);
  }

  public function enqueue(): void
  {
    $version = defined('CLOAKWP_INLINE_FORMATS_VERSION')
      ? CLOAKWP_INLINE_FORMATS_VERSION
      : '0.1.0';

    $assetPath = $this->path('js/build/index.asset.php');
    $scriptPath = $this->path('js/build/index.js');

    if (!is_readable($scriptPath)) {
      return;
    }

    // wp-format-library must load first so we can unregister core/bold etc.
    $deps = [
      'wp-rich-text',
      'wp-block-editor',
      'wp-components',
      'wp-element',
      'wp-i18n',
      'wp-data',
      'wp-format-library',
    ];
    $assetVersion = $version;

    if (is_readable($assetPath)) {
      /** @var array{dependencies?: list<string>, version?: string} $asset */
      $asset = require $assetPath;
      if (isset($asset['dependencies']) && is_array($asset['dependencies'])) {
        $deps = array_values(array_unique(array_merge($asset['dependencies'], ['wp-format-library'])));
      }
      if (isset($asset['version']) && is_string($asset['version'])) {
        $assetVersion = $asset['version'];
      }
    } else {
      $assetVersion = $version . '.' . (string) filemtime($scriptPath);
    }

    $scriptUrl = $this->url('js/build/index.js');

    if (wp_script_is(self::SCRIPT_HANDLE, 'registered')) {
      $scripts = wp_scripts();
      $scripts->registered[self::SCRIPT_HANDLE]->src = $scriptUrl;
      $scripts->registered[self::SCRIPT_HANDLE]->deps = $deps;
      $scripts->registered[self::SCRIPT_HANDLE]->ver = $assetVersion;
    } else {
      wp_register_script(self::SCRIPT_HANDLE, $scriptUrl, $deps, $assetVersion, true);
    }

    wp_enqueue_script(self::SCRIPT_HANDLE);
    $this->localize();
  }

  private function localize(): void
  {
    if ($this->localized || !wp_script_is(self::SCRIPT_HANDLE, 'registered')) {
      return;
    }

    wp_localize_script(self::SCRIPT_HANDLE, 'inlineFormatsEditor', [
      'formats' => $this->config->toEditorConfig(),
    ]);

    $this->localized = true;
  }

  private function url(string $relative): string
  {
    $relative = ltrim($relative, '/');
    $base = plugins_url('', $this->pluginFile);

    // Symlink safety net: plugins_url() can emit /app/plugins/var/www/... when
    // the package realpath sits outside wp-content.
    if (defined('WPMU_PLUGIN_URL') && (str_contains($base, '/var/www/') || str_contains($base, '/plugins/var/'))) {
      $base = trailingslashit(WPMU_PLUGIN_URL) . 'inline-formats';
    }

    return trailingslashit($base) . $relative;
  }

  private function path(string $relative): string
  {
    // Read from the real package dir (symlink target) so filemtime/is_readable work.
    $dir = defined('CLOAKWP_INLINE_FORMATS_DIR')
      ? CLOAKWP_INLINE_FORMATS_DIR
      : dirname($this->pluginFile);

    $real = realpath($dir);
    if ($real !== false) {
      $dir = $real;
    }

    return rtrim($dir, '/\\') . '/' . ltrim($relative, '/');
  }
}
