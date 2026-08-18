<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Format;

use InvalidArgumentException;

/**
 * On/off format that writes a fixed style declaration when active.
 *
 * @extends InlineFormat<ToggleFormat>
 */
final class ToggleFormat extends InlineFormat
{
  /**
   * @param list<string>|null $blocks
   */
  private function __construct(
    string $formatName,
    string $title,
    string $tagName,
    string $className,
    ?string $icon,
    ?array $blocks,
    private readonly string $style,
  ) {
    parent::__construct($formatName, $title, $tagName, $className, $icon, $blocks);
  }

  public static function make(string $name): self
  {
    $qualified = self::qualifyName($name);
    $slug = sanitize_key(str_replace('/', '-', $qualified));

    return new self(
      formatName: $qualified,
      title: $name,
      tagName: 'span',
      className: 'has-inline-' . $slug,
      icon: null,
      blocks: null,
      style: '',
    );
  }

  /**
   * CSS declaration(s), e.g. "font-variant: small-caps" or "font-weight: 500; letter-spacing: 0.02em".
   */
  public function style(string $style): self
  {
    $style = trim($style);
    if ($style === '') {
      throw new InvalidArgumentException('ToggleFormat style cannot be empty.');
    }

    return $this->cloneWith(style: $style);
  }

  public function toEditorConfig(): array
  {
    if ($this->style === '') {
      throw new InvalidArgumentException(
        sprintf('ToggleFormat "%s" requires ->style(...) before register().', $this->formatName)
      );
    }

    return array_merge($this->baseEditorConfig(), [
      'control' => [
        'type' => 'toggle',
        'style' => $this->style,
      ],
    ]);
  }

  protected function with(
    ?string $title = null,
    ?string $tagName = null,
    ?string $className = null,
    ?string $icon = null,
    bool $iconSet = false,
    ?array $blocks = null,
    bool $blocksSet = false,
  ): static {
    return $this->cloneWith(
      title: $title,
      tagName: $tagName,
      className: $className,
      icon: $icon,
      iconSet: $iconSet,
      blocks: $blocks,
      blocksSet: $blocksSet,
    );
  }

  /**
   * @param list<string>|null $blocks
   */
  private function cloneWith(
    ?string $title = null,
    ?string $tagName = null,
    ?string $className = null,
    ?string $icon = null,
    bool $iconSet = false,
    ?array $blocks = null,
    bool $blocksSet = false,
    ?string $style = null,
  ): self {
    return new self(
      formatName: $this->formatName,
      title: $title ?? $this->title,
      tagName: $tagName ?? $this->tagName,
      className: $className ?? $this->className,
      icon: $iconSet ? $icon : $this->icon,
      blocks: $blocksSet ? $blocks : $this->blocks,
      style: $style ?? $this->style,
    );
  }

  private static function qualifyName(string $name): string
  {
    if (str_contains($name, '/')) {
      return $name;
    }

    return 'cloakwp/' . sanitize_key($name);
  }
}
