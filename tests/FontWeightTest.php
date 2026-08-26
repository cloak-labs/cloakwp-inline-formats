<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Tests;

use CloakWP\InlineFormats\Format\FontWeight;
use CloakWP\InlineFormats\Format\ToggleFormat;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FontWeightTest extends TestCase
{
  public function testDefaultEditorConfig(): void
  {
    $config = FontWeight::make()->toEditorConfig();

    $this->assertSame('cloakwp/font-weight', $config['name']);
    $this->assertSame('Font weight', $config['title']);
    $this->assertSame('span', $config['tagName']);
    $this->assertSame('has-inline-font-weight', $config['className']);
    $this->assertSame(['style' => 'style'], $config['attributes']);
    $this->assertSame('toolbar', $config['placement']);
    $this->assertSame(['core/bold'], $config['unregister']);
    $this->assertSame('choice', $config['control']['type']);
    $this->assertSame('font-weight', $config['control']['styleProperty']);

    $values = array_column($config['control']['options'], 'value');
    $this->assertSame(['300', '400', '500', '600', '700'], $values);

    $labels = array_column($config['control']['options'], 'label');
    $this->assertContains('Medium', $labels);
    $this->assertContains('Semibold', $labels);
  }

  public function testInDropdownKeepsCoreBold(): void
  {
    $config = FontWeight::make()->inDropdown()->toEditorConfig();

    $this->assertSame('dropdown', $config['placement']);
    $this->assertArrayNotHasKey('unregister', $config);
  }

  public function testKeepBoldOnToolbar(): void
  {
    $config = FontWeight::make()->inToolbar()->keepBold()->toEditorConfig();

    $this->assertSame('toolbar', $config['placement']);
    $this->assertArrayNotHasKey('unregister', $config);
  }

  public function testWeightsListRestrictsOptions(): void
  {
    $config = FontWeight::make()->weights([400, 500, 700])->toEditorConfig();
    $values = array_column($config['control']['options'], 'value');

    $this->assertSame(['400', '500', '700'], $values);
  }

  public function testWeightsMapAllowsCustomLabels(): void
  {
    $config = FontWeight::make()->weights([
      400 => 'Book',
      500 => 'Medium',
    ])->toEditorConfig();

    $this->assertSame(
      [
        ['label' => 'Book', 'value' => '400'],
        ['label' => 'Medium', 'value' => '500'],
      ],
      $config['control']['options']
    );
  }

  public function testAllCssWeightsIncludesFullRange(): void
  {
    $config = FontWeight::make()->allCssWeights()->toEditorConfig();
    $values = array_column($config['control']['options'], 'value');

    $this->assertCount(9, $values);
    $this->assertSame('100', $values[0]);
    $this->assertSame('900', $values[8]);
  }

  public function testEmptyWeightsThrows(): void
  {
    $this->expectException(InvalidArgumentException::class);
    FontWeight::make()->weights([]);
  }

  public function testBlocksFilterIsSerialized(): void
  {
    $config = FontWeight::make()
      ->blocks(['core/paragraph', 'core/heading'])
      ->toEditorConfig();

    $this->assertSame(['core/paragraph', 'core/heading'], $config['blocks']);
  }
}
