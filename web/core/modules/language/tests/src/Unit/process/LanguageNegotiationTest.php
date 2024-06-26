<?php

declare(strict_types=1);

namespace Drupal\Tests\language\Unit\process;

use Drupal\language\Plugin\migrate\process\LanguageNegotiation;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;
use Drupal\migrate\MigrateException;

/**
 * @coversDefaultClass \Drupal\language\Plugin\migrate\process\LanguageNegotiation
 * @group language
 */
class LanguageNegotiationTest extends MigrateProcessTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->plugin = new LanguageNegotiation([], 'map', []);
    parent::setUp();
  }

  /**
   * Tests successful transformation without weights.
   */
  public function testTransformWithWeights(): void {
    $source = [
      [
        'locale-url' => [],
        'language-default' => [],
      ],
      [
        'locale-url' => -10,
        'locale-session' => -9,
        'locale-user' => -8,
        'locale-browser' => -7,
        'language-default' => -6,
      ],
    ];
    $expected = [
      'enabled' => [
        'language-url' => -10,
        'language-selected' => -6,
      ],
      'method_weights' => [
        'language-url' => -10,
        'language-session' => -9,
        'language-user' => -8,
        'language-browser' => -7,
        'language-selected' => -6,
      ],
    ];
    $value = $this->plugin->transform($source, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($value, $expected);
  }

  /**
   * Tests successful transformation without weights.
   */
  public function testTransformWithoutWeights(): void {
    $source = [
      [
        'locale-url' => [],
        'locale-url-fallback' => [],
      ],
    ];
    $expected = [
      'enabled' => [
        'language-url' => 0,
        'language-url-fallback' => 1,
      ],
    ];
    $value = $this->plugin->transform($source, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($value, $expected);
  }

  /**
   * Tests string input.
   */
  public function testStringInput(): void {
    $this->plugin = new LanguageNegotiation([], 'map', []);
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage('The input should be an array');
    $this->plugin->transform('foo', $this->migrateExecutable, $this->row, 'destination_property');
  }

}
