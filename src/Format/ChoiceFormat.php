<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Format;

use InvalidArgumentException;

/**
 * Dropdown format that writes a single CSS property into style="…".
 *
 * @extends InlineFormat<ChoiceFormat>
 */
final class ChoiceFormat extends InlineFormat
{
  /**
   * @param list<array{label: string, value: string}> $options
   * @param list<string>|null $blocks
   * @param list<string> $unregister
   */
  private function __construct(
    string $formatName,
    string $title,
    string $tagName,
    string $className,
    ?string $icon,
    ?array $blocks,
    string $placement,
    array $unregister,
    private readonly string $styleProperty,
    private readonly array $options,
  ) {
    parent::__construct(
      $formatName,
      $title,
      $tagName,
      $className,
      $icon,
      $blocks,
      $placement,
      $unregister,
    );

    if ($styleProperty === '') {
      throw new InvalidArgumentException('styleProperty cannot be empty.');
    }
    if ($options === []) {
      throw new InvalidArgumentException('ChoiceFormat requires at least one option.');
    }
  }

  /**
   * @param list<array{label: string, value: string|int}>|array<string|int, string> $options
   *        Either a list of {label, value} maps, or value => label map.
   */
  public static function make(string $name, string $styleProperty, array $options): self
  {
    $normalized = self::normalizeOptions($options);

    return new self(
      formatName: self::qualifyName($name),
      title: $name,
      tagName: 'span',
      className: 'has-inline-' . sanitize_key(str_replace('/', '-', self::qualifyName($name))),
      icon: null,
      blocks: null,
      placement: self::PLACEMENT_DROPDOWN,
      unregister: [],
      styleProperty: $styleProperty,
      options: $normalized,
    );
  }

  /**
   * @param list<array{label: string, value: string|int}>|array<string|int, string> $options
   */
  public function options(array $options): self
  {
    return $this->cloneWith(options: self::normalizeOptions($options));
  }

  public function styleProperty(string $styleProperty): self
  {
    return $this->cloneWith(styleProperty: $styleProperty);
  }

  public function toEditorConfig(): array
  {
    return array_merge($this->baseEditorConfig(), [
      'control' => [
        'type' => 'choice',
        'styleProperty' => $this->styleProperty,
        'options' => $this->options,
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
    ?string $placement = null,
    ?array $unregister = null,
    bool $unregisterSet = false,
  ): static {
    return $this->cloneWith(
      title: $title,
      tagName: $tagName,
      className: $className,
      icon: $icon,
      iconSet: $iconSet,
      blocks: $blocks,
      blocksSet: $blocksSet,
      placement: $placement,
      unregister: $unregister,
      unregisterSet: $unregisterSet,
    );
  }

  /**
   * @param list<array{label: string, value: string}>|null $options
   * @param list<string>|null $blocks
   * @param list<string>|null $unregister
   */
  private function cloneWith(
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
    ?string $styleProperty = null,
    ?array $options = null,
  ): self {
    return new self(
      formatName: $this->formatName,
      title: $title ?? $this->title,
      tagName: $tagName ?? $this->tagName,
      className: $className ?? $this->className,
      icon: $iconSet ? $icon : $this->icon,
      blocks: $blocksSet ? $blocks : $this->blocks,
      placement: $placement ?? $this->placement,
      unregister: $unregisterSet ? ($unregister ?? []) : $this->unregister,
      styleProperty: $styleProperty ?? $this->styleProperty,
      options: $options ?? $this->options,
    );
  }

  private static function qualifyName(string $name): string
  {
    if (str_contains($name, '/')) {
      return $name;
    }

    return 'cloakwp/' . sanitize_key($name);
  }

  /**
   * @param list<array{label: string, value: string|int}>|array<string|int, string> $options
   * @return list<array{label: string, value: string}>
   */
  private static function normalizeOptions(array $options): array
  {
    if ($options === []) {
      return [];
    }

    $isList = array_is_list($options);
    $out = [];

    if ($isList) {
      foreach ($options as $option) {
        if (!is_array($option) || !isset($option['value'])) {
          throw new InvalidArgumentException(
            'List options must be arrays with at least a "value" key.'
          );
        }
        $value = (string) $option['value'];
        $label = isset($option['label']) ? (string) $option['label'] : $value;
        $out[] = ['label' => $label, 'value' => $value];
      }

      return $out;
    }

    foreach ($options as $value => $label) {
      $out[] = [
        'label' => (string) $label,
        'value' => (string) $value,
      ];
    }

    return $out;
  }
}
