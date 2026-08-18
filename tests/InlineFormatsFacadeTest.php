<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Tests;

use CloakWP\InlineFormats\Core\Config;
use CloakWP\InlineFormats\Format\FontWeight;
use CloakWP\InlineFormats\Format\ToggleFormat;
use CloakWP\InlineFormats\InlineFormats;
use PHPUnit\Framework\TestCase;

final class InlineFormatsFacadeTest extends TestCase
{
  public function testDefaultsIncludeFontWeight(): void
  {
    $config = Config::defaults();
    $editor = $config->toEditorConfig();

    $this->assertCount(1, $editor);
    $this->assertSame('cloakwp/font-weight', $editor[0]['name']);
  }

  public function testAddReplacesFormatByName(): void
  {
    $instance = InlineFormats::make()
      ->add(FontWeight::make()->weights([500, 700]));

    $editor = $instance->config()->toEditorConfig();

    $this->assertCount(1, $editor);
    $this->assertSame('cloakwp/font-weight', $editor[0]['name']);
    $this->assertSame(
      ['500', '700'],
      array_column($editor[0]['control']['options'], 'value')
    );
  }

  public function testAddAppendsNewFormat(): void
  {
    $instance = InlineFormats::make()
      ->add(
        ToggleFormat::make('small-caps')
          ->title('Small caps')
          ->style('font-variant: small-caps')
      );

    $names = array_column($instance->config()->toEditorConfig(), 'name');

    $this->assertSame(
      ['cloakwp/font-weight', 'cloakwp/small-caps'],
      $names
    );
  }

  public function testExceptRemovesByName(): void
  {
    $instance = InlineFormats::make()
      ->add(
        ToggleFormat::make('small-caps')
          ->style('font-variant: small-caps')
      )
      ->except(['cloakwp/font-weight']);

    $names = array_column($instance->config()->toEditorConfig(), 'name');

    $this->assertSame(['cloakwp/small-caps'], $names);
  }

  public function testNotBootedUntilRegister(): void
  {
    InlineFormats::make();
    $this->assertFalse(InlineFormats::booted());
  }
}
