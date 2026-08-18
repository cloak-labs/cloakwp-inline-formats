<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Plugin;

use CloakWP\InlineFormats\Core\Config;

/**
 * WordPress integration layer (hooks, editor assets).
 */
final class Plugin
{
  public function __construct(
    private readonly Config $config,
    private readonly string $pluginFile,
  ) {
  }

  public function boot(): void
  {
    $assets = new Assets($this->config, $this->pluginFile);
    $assets->register();

    add_filter('safe_style_css', [$this, 'allowStyleProperties']);
  }

  /**
   * Ensure CSS properties used by registered formats survive kses.
   *
   * @param list<string> $styles
   * @return list<string>
   */
  public function allowStyleProperties(array $styles): array
  {
    $needed = ['font-weight', 'font-variant', 'font-style', 'letter-spacing', 'text-transform'];
    foreach ($needed as $property) {
      if (!in_array($property, $styles, true)) {
        $styles[] = $property;
      }
    }

    return $styles;
  }
}
