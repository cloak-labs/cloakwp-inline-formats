<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats;

use CloakWP\InlineFormats\Core\Config;
use CloakWP\InlineFormats\Core\Contract\Format;
use CloakWP\InlineFormats\Plugin\Plugin;
use InvalidArgumentException;

/**
 * Fluent entry point for Inline Formats.
 *
 * @example
 * InlineFormats::make()
 *   ->add(FontWeight::make()->weights([400, 500, 700]))
 *   ->register();
 */
final class InlineFormats
{
  private static ?self $instance = null;

  private Config $config;
  private bool $registered = false;
  private ?Plugin $plugin = null;

  private function __construct(Config $config)
  {
    $this->config = $config;
  }

  public static function make(): self
  {
    return new self(Config::defaults());
  }

  public static function booted(): bool
  {
    return self::$instance !== null && self::$instance->registered;
  }

  public static function instance(): ?self
  {
    return self::$instance;
  }

  /**
   * Add or replace a format (keyed by Format::name()).
   */
  public function add(Format $format): self
  {
    $this->assertMutable();
    $this->config = $this->config->withFormat($format);

    return $this;
  }

  /**
   * Remove formats by name.
   *
   * @param list<string> $names
   */
  public function except(array $names): self
  {
    $this->assertMutable();
    $this->config = $this->config->withoutFormats($names);

    return $this;
  }

  public function config(): Config
  {
    return $this->config;
  }

  /**
   * Boot WordPress integration (editor assets + format registration).
   */
  public function register(): self
  {
    if ($this->registered) {
      return $this;
    }

    if (self::$instance !== null && self::$instance->registered && self::$instance !== $this) {
      throw new InvalidArgumentException(
        'Inline Formats is already registered. Call InlineFormats::make()->…->register() only once.'
      );
    }

    /** @var Config $config */
    $config = apply_filters('cloakwp/inline-formats/config', $this->config);
    $this->config = $config;

    $pluginFile = defined('CLOAKWP_INLINE_FORMATS_FILE')
      ? CLOAKWP_INLINE_FORMATS_FILE
      : dirname(__DIR__) . '/inline-formats.php';

    $this->plugin = new Plugin($this->config, $pluginFile);
    $this->plugin->boot();

    $this->registered = true;
    self::$instance = $this;

    return $this;
  }

  private function assertMutable(): void
  {
    if ($this->registered) {
      throw new InvalidArgumentException('Cannot change Inline Formats config after register().');
    }
  }
}
