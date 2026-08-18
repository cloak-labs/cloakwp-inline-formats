<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Core;

use CloakWP\InlineFormats\Core\Contract\Format;
use CloakWP\InlineFormats\Format\FontWeight;

/**
 * Immutable configuration for inline formats.
 */
final class Config
{
  /**
   * @param array<string, Format> $formats Formats keyed by name()
   */
  public function __construct(
    public readonly array $formats,
  ) {
  }

  public static function defaults(): self
  {
    $fontWeight = FontWeight::make();

    return new self([
      $fontWeight->name() => $fontWeight,
    ]);
  }

  public function withFormat(Format $format): self
  {
    $formats = $this->formats;
    $formats[$format->name()] = $format;

    return new self($formats);
  }

  /**
   * @param list<string> $names Format names to remove
   */
  public function withoutFormats(array $names): self
  {
    $formats = $this->formats;
    foreach ($names as $name) {
      unset($formats[$name]);
    }

    return new self($formats);
  }

  /**
   * @return list<array<string, mixed>>
   */
  public function toEditorConfig(): array
  {
    $out = [];
    foreach ($this->formats as $format) {
      $out[] = $format->toEditorConfig();
    }

    return $out;
  }
}
