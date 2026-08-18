<?php

declare(strict_types=1);

namespace CloakWP\InlineFormats\Tests;

use CloakWP\InlineFormats\Format\ToggleFormat;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ToggleFormatTest extends TestCase
{
  public function testEditorConfigRequiresStyle(): void
  {
    $this->expectException(InvalidArgumentException::class);
    ToggleFormat::make('small-caps')->toEditorConfig();
  }

  public function testFluentSerialization(): void
  {
    $config = ToggleFormat::make('small-caps')
      ->title('Small caps')
      ->className('has-inline-small-caps')
      ->style('font-variant: small-caps')
      ->toEditorConfig();

    $this->assertSame('cloakwp/small-caps', $config['name']);
    $this->assertSame('Small caps', $config['title']);
    $this->assertSame('has-inline-small-caps', $config['className']);
    $this->assertSame('toggle', $config['control']['type']);
    $this->assertSame('font-variant: small-caps', $config['control']['style']);
  }

  public function testQualifiedNameIsPreserved(): void
  {
    $config = ToggleFormat::make('my-plugin/accent')
      ->style('color: red')
      ->toEditorConfig();

    $this->assertSame('my-plugin/accent', $config['name']);
  }
}
