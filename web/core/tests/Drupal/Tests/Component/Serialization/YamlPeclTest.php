<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Serialization;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\YamlPecl;

/**
 * Tests the YamlPecl serialization implementation.
 *
 * @group Drupal
 * @group Serialization
 * @coversDefaultClass \Drupal\Component\Serialization\YamlPecl
 * @requires extension yaml
 */
class YamlPeclTest extends YamlTestBase {

  /**
   * Tests encoding and decoding basic data structures.
   *
   * @covers ::encode
   * @covers ::decode
   * @dataProvider providerEncodeDecodeTests
   */
  public function testEncodeDecode(array $data): void {
    $this->assertEquals($data, YamlPecl::decode(YamlPecl::encode($data)));
  }

  /**
   * Ensures that php object support is disabled.
   */
  public function testObjectSupportDisabled(): void {
    $object = new \stdClass();
    $object->foo = 'bar';
    $this->assertEquals(['O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}'], YamlPecl::decode(YamlPecl::encode([$object])));
    $this->assertEquals(0, ini_get('yaml.decode_php'));
  }

  /**
   * Tests decoding YAML node anchors.
   *
   * @covers ::decode
   * @dataProvider providerDecodeTests
   */
  public function testDecode($string, $data): void {
    $this->assertEquals($data, YamlPecl::decode($string));
  }

  /**
   * Tests our encode settings.
   *
   * @covers ::encode
   */
  public function testEncode(): void {
    // cSpell:disable
    $this->assertEquals('---
foo:
  bar: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sapien ex, venenatis vitae nisi eu, posuere luctus dolor. Nullam convallis
...
', YamlPecl::encode(['foo' => ['bar' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sapien ex, venenatis vitae nisi eu, posuere luctus dolor. Nullam convallis']]));
    // cSpell:enable
  }

  /**
   * Tests YAML boolean callback.
   *
   * @param string $string
   *   String value for the YAML boolean.
   * @param string|bool $expected
   *   The expected return value.
   *
   * @covers ::applyBooleanCallbacks
   * @dataProvider providerBoolTest
   */
  public function testApplyBooleanCallbacks($string, $expected): void {
    $this->assertEquals($expected, YamlPecl::applyBooleanCallbacks($string, 'bool', NULL));
  }

  /**
   * @covers ::getFileExtension
   */
  public function testGetFileExtension(): void {
    $this->assertEquals('yml', YamlPecl::getFileExtension());
  }

  /**
   * Tests that invalid YAML throws an exception.
   *
   * @covers ::errorHandler
   */
  public function testError(): void {
    $this->expectException(InvalidDataTypeException::class);
    YamlPecl::decode('foo: [ads');
  }

}
