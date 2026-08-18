<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Core\Contract;

/**
 * A Gutenberg inline format that can be serialized for the editor JS engine.
 */
interface Format
{
  /**
   * Unique format name, e.g. "cloakwp/font-weight".
   */
  public function name(): string;

  /**
   * Payload consumed by the editor JS engine (registerFormatType + controls).
   *
   * @return array<string, mixed>
   */
  public function toEditorConfig(): array;
}
