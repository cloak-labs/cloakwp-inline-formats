<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Format;

use CloakWP\InlineFormats\Core\Contract\Format;
use InvalidArgumentException;

/**
 * Built-in choice format for inline font-weight.
 *
 * Defaults to the block toolbar and replaces core Bold (same control surface).
 * Use ->inDropdown() to keep core Bold and move this into the overflow menu.
 */
final class FontWeight implements Format
{
  public const NAME = 'cloakwp/font-weight';

  /** @var array<int, string> */
  private const DEFAULT_LABELS = [
    100 => 'Thin',
    200 => 'Extra Light',
    300 => 'Light',
    400 => 'Regular',
    500 => 'Medium',
    600 => 'Semibold',
    700 => 'Bold',
    800 => 'Extra Bold',
    900 => 'Black',
  ];

  /** @var list<int> */
  private const DEFAULT_WEIGHTS = [300, 400, 500, 600, 700];

  private ChoiceFormat $format;
  private bool $replaceBold;

  private function __construct(ChoiceFormat $format, bool $replaceBold)
  {
    $this->format = $format;
    $this->replaceBold = $replaceBold;
  }

  public static function make(): self
  {
    return new self(
      ChoiceFormat::make(self::NAME, 'font-weight', self::optionsFromWeights(self::DEFAULT_WEIGHTS))
        ->title('Font weight')
        ->className('has-inline-font-weight')
        ->icon('editor-bold')
        ->inToolbar(),
      replaceBold: true,
    );
  }

  /**
   * Restrict or relabel weights.
   *
   * Accepts:
   * - `[400, 500, 700]` — numeric list with default labels
   * - `[400 => 'Book', 500 => 'Medium']` — value => label map
   * - `[['label' => 'Medium', 'value' => 500], …]` — explicit list
   *
   * @param list<int|string>|array<int|string, string>|list<array{label: string, value: string|int}> $weights
   */
  public function weights(array $weights): self
  {
    if ($weights === []) {
      throw new InvalidArgumentException('FontWeight requires at least one weight.');
    }

    if (array_is_list($weights) && isset($weights[0]) && is_array($weights[0])) {
      /** @var list<array{label: string, value: string|int}> $weights */
      return new self($this->format->options($weights), $this->replaceBold);
    }

    if (array_is_list($weights)) {
      $ints = [];
      foreach ($weights as $w) {
        $ints[] = (int) $w;
      }

      return new self($this->format->options(self::optionsFromWeights($ints)), $this->replaceBold);
    }

    /** @var array<int|string, string> $weights */
    return new self($this->format->options($weights), $this->replaceBold);
  }

  /**
   * Include every CSS weight from 100–900.
   */
  public function allCssWeights(): self
  {
    return $this->weights(array_keys(self::DEFAULT_LABELS));
  }

  public function title(string $title): self
  {
    return new self($this->format->title($title), $this->replaceBold);
  }

  public function className(string $className): self
  {
    return new self($this->format->className($className), $this->replaceBold);
  }

  public function icon(?string $icon): self
  {
    return new self($this->format->icon($icon), $this->replaceBold);
  }

  /**
   * @param list<string>|null $blocks
   */
  public function blocks(?array $blocks): self
  {
    return new self($this->format->blocks($blocks), $this->replaceBold);
  }

  /**
   * Main block toolbar. Replaces core Bold by default (use keepBold() to opt out).
   */
  public function inToolbar(): self
  {
    return new self($this->format->inToolbar(), true);
  }

  /**
   * Formatting overflow (▾). Keeps core Bold.
   */
  public function inDropdown(): self
  {
    return new self($this->format->inDropdown(), false);
  }

  public function placement(string $placement): self
  {
    if ($placement === ChoiceFormat::PLACEMENT_TOOLBAR) {
      return $this->inToolbar();
    }

    if ($placement === ChoiceFormat::PLACEMENT_DROPDOWN) {
      return $this->inDropdown();
    }

    return new self($this->format->placement($placement), $this->replaceBold);
  }

  /**
   * Unregister core/bold when this format loads (default when inToolbar()).
   */
  public function replaceBold(bool $replace = true): self
  {
    return new self($this->format, $replace);
  }

  /**
   * Keep the native Bold button alongside Font weight.
   */
  public function keepBold(): self
  {
    return $this->replaceBold(false);
  }

  public function name(): string
  {
    return $this->format->name();
  }

  /**
   * @return array<string, mixed>
   */
  public function toEditorConfig(): array
  {
    $format = $this->replaceBold
      ? $this->format->unregister(['core/bold'])
      : $this->format->unregister([]);

    return $format->toEditorConfig();
  }

  /**
   * Expose the underlying ChoiceFormat for Config typing.
   */
  public function toFormat(): ChoiceFormat
  {
    return $this->replaceBold
      ? $this->format->unregister(['core/bold'])
      : $this->format->unregister([]);
  }

  /**
   * @param list<int> $weights
   * @return array<int, string>
   */
  private static function optionsFromWeights(array $weights): array
  {
    $out = [];
    foreach ($weights as $weight) {
      $out[$weight] = self::DEFAULT_LABELS[$weight] ?? (string) $weight;
    }

    return $out;
  }
}
