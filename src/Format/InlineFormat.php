<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Format;

use CloakWP\InlineFormats\Core\Contract\Format;
use InvalidArgumentException;

/**
 * Shared fluent base for inline formats (tag, class, title, icon, block filter, placement).
 *
 * @template T of self
 */
abstract class InlineFormat implements Format
{
  public const PLACEMENT_TOOLBAR = 'toolbar';
  public const PLACEMENT_DROPDOWN = 'dropdown';

  /**
   * @param list<string>|null $blocks Null = all rich-text contexts
   * @param list<string> $unregister Format names to unregister when this format loads
   */
  protected function __construct(
    protected readonly string $formatName,
    protected readonly string $title,
    protected readonly string $tagName,
    protected readonly string $className,
    protected readonly ?string $icon,
    protected readonly ?array $blocks,
    protected readonly string $placement,
    protected readonly array $unregister,
  ) {
    if ($formatName === '') {
      throw new InvalidArgumentException('Format name cannot be empty.');
    }
    if ($className === '' && $tagName === 'span') {
      throw new InvalidArgumentException(
        'span formats require a non-empty className (Gutenberg already uses bare span).'
      );
    }
    if (!in_array($placement, [self::PLACEMENT_TOOLBAR, self::PLACEMENT_DROPDOWN], true)) {
      throw new InvalidArgumentException(
        'placement must be "toolbar" (block toolbar) or "dropdown" (formatting overflow).'
      );
    }
  }

  public function name(): string
  {
    return $this->formatName;
  }

  /**
   * @return T
   */
  public function title(string $title): static
  {
    return $this->with(title: $title);
  }

  /**
   * @return T
   */
  public function tagName(string $tagName): static
  {
    return $this->with(tagName: $tagName);
  }

  /**
   * @return T
   */
  public function className(string $className): static
  {
    return $this->with(className: $className);
  }

  /**
   * Dashicon slug or null (engine may supply a default).
   *
   * @return T
   */
  public function icon(?string $icon): static
  {
    return $this->with(icon: $icon, iconSet: true);
  }

  /**
   * Limit the control to specific block names. Null = all rich-text contexts.
   *
   * @param list<string>|null $blocks
   * @return T
   */
  public function blocks(?array $blocks): static
  {
    return $this->with(
      blocks: $blocks === null ? null : array_values($blocks),
      blocksSet: true,
    );
  }

  /**
   * Show on the main block toolbar (beside bold/italic when using BlockControls).
   *
   * @return T
   */
  public function inToolbar(): static
  {
    return $this->with(placement: self::PLACEMENT_TOOLBAR);
  }

  /**
   * Show in the formatting overflow menu (▾ next to bold/italic).
   *
   * @return T
   */
  public function inDropdown(): static
  {
    return $this->with(placement: self::PLACEMENT_DROPDOWN);
  }

  /**
   * Alias for placement — "toolbar" | "dropdown".
   *
   * @return T
   */
  public function placement(string $placement): static
  {
    return $this->with(placement: $placement);
  }

  /**
   * Unregister other format types when this one loads (e.g. core/bold).
   *
   * @param list<string> $names
   * @return T
   */
  public function unregister(array $names): static
  {
    return $this->with(unregister: array_values($names), unregisterSet: true);
  }

  /**
   * @return array<string, mixed>
   */
  protected function baseEditorConfig(): array
  {
    $config = [
      'name' => $this->formatName,
      'title' => $this->title,
      'tagName' => $this->tagName,
      'className' => $this->className,
      'placement' => $this->placement,
      'attributes' => [
        'style' => 'style',
      ],
    ];

    if ($this->icon !== null) {
      $config['icon'] = $this->icon;
    }

    if ($this->blocks !== null) {
      $config['blocks'] = $this->blocks;
    }

    if ($this->unregister !== []) {
      $config['unregister'] = $this->unregister;
    }

    return $config;
  }

  /**
   * @param list<string>|null $blocks
   * @param list<string>|null $unregister
   * @return T
   */
  abstract protected function with(
    ?string $title = null,
    ?string $tagName = null,
    ?string $className = null,
    ?string $icon = null,
    bool $iconSet = false,
    ?array $blocks = null,
    bool $blocksSet = false,
    ?string $placement = null,
    ?array $unregister = null,
    bool $unregisterSet = false,
  ): static;
}
