<?php

declare(strict_types=1);

namespace Drupal\Tests\search\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\search\SearchTextProcessorInterface;

// cspell:ignore bopomofo jamo lisu

/**
 * Tests that CJK tokenizer works as intended.
 *
 * @group search
 */
class SearchTokenizerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['search'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['search']);
  }

  /**
   * Verifies that strings of CJK characters are tokenized.
   *
   * The text analysis function does special things with numbers, symbols
   * and punctuation. So we only test that CJK characters that are not in these
   * character classes are tokenized properly. See PREG_CLASS_CKJ for more
   * information.
   */
  public function testTokenizer(): void {
    // Set the minimum word size to 1 (to split all CJK characters) and make
    // sure CJK tokenizing is turned on.
    $this->config('search.settings')
      ->set('index.minimum_word_size', 1)
      ->set('index.overlap_cjk', TRUE)
      ->save();

    // Create a string of CJK characters from various character ranges in the
    // Unicode tables.

    // Beginnings of the character ranges.
    $starts = [
      'CJK unified' => 0x4e00,
      'CJK Ext A' => 0x3400,
      'CJK Compat' => 0xf900,
      'Hangul Jamo' => 0x1100,
      'Hangul Ext A' => 0xa960,
      'Hangul Ext B' => 0xd7b0,
      'Hangul Compat' => 0x3131,
      'Half non-punct 1' => 0xff21,
      'Half non-punct 2' => 0xff41,
      'Half non-punct 3' => 0xff66,
      'Hangul Syllables' => 0xac00,
      'Hiragana' => 0x3040,
      'Katakana' => 0x30a1,
      'Katakana Ext' => 0x31f0,
      'CJK Reserve 1' => 0x20000,
      'CJK Reserve 2' => 0x30000,
      'Bopomofo' => 0x3100,
      'Bopomofo Ext' => 0x31a0,
      'Lisu' => 0xa4d0,
      'Yi' => 0xa000,
    ];

    // Ends of the character ranges.
    $ends = [
      'CJK unified' => 0x9fcf,
      'CJK Ext A' => 0x4dbf,
      'CJK Compat' => 0xfaff,
      'Hangul Jamo' => 0x11ff,
      'Hangul Ext A' => 0xa97f,
      'Hangul Ext B' => 0xd7ff,
      'Hangul Compat' => 0x318e,
      'Half non-punct 1' => 0xff3a,
      'Half non-punct 2' => 0xff5a,
      'Half non-punct 3' => 0xffdc,
      'Hangul Syllables' => 0xd7af,
      'Hiragana' => 0x309f,
      'Katakana' => 0x30ff,
      'Katakana Ext' => 0x31ff,
      'CJK Reserve 1' => 0x2fffd,
      'CJK Reserve 2' => 0x3fffd,
      'Bopomofo' => 0x312f,
      'Bopomofo Ext' => 0x31b7,
      'Lisu' => 0xa4fd,
      'Yi' => 0xa48f,
    ];

    // Generate characters consisting of starts, midpoints, and ends.
    $chars = [];
    foreach ($starts as $key => $value) {
      $chars[] = $this->code2utf($starts[$key]);
      $mid = round(0.5 * ($starts[$key] + $ends[$key]));
      $chars[] = $this->code2utf($mid);
      $chars[] = $this->code2utf($ends[$key]);
    }

    // Merge into a string and tokenize.
    $string = implode('', $chars);
    $text_processor = \Drupal::service('search.text_processor');
    assert($text_processor instanceof SearchTextProcessorInterface);
    $out = trim($text_processor->analyze($string));
    $expected = mb_strtolower(implode(' ', $chars));

    // Verify that the output matches what we expect.
    $this->assertEquals($expected, $out, 'CJK tokenizer worked on all supplied CJK characters');
  }

  /**
   * Verifies that strings of non-CJK characters are not tokenized.
   *
   * This is just a sanity check - it verifies that strings of letters are
   * not tokenized.
   */
  public function testNoTokenizer(): void {
    // Set the minimum word size to 1 (to split all CJK characters) and make
    // sure CJK tokenizing is turned on.
    $this->config('search.settings')
      ->set('index.minimum_word_size', 1)
      ->set('index.overlap_cjk', TRUE)
      ->save();

    $letters = 'abcdefghijklmnopqrstuvwxyz';
    $text_processor = \Drupal::service('search.text_processor');
    assert($text_processor instanceof SearchTextProcessorInterface);
    $out = trim($text_processor->analyze($letters));

    $this->assertEquals($letters, $out, 'Letters are not CJK tokenized');
  }

  /**
   * Like PHP chr() function, but for unicode characters.
   *
   * Function chr() only works for ASCII characters up to character 255. This
   * function converts a number to the corresponding unicode character. Adapted
   * from functions supplied in comments on several functions on php.net.
   */
  public function code2utf($num) {
    if ($num < 128) {
      return chr($num);
    }

    if ($num < 2048) {
      return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    }

    if ($num < 65536) {
      return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }

    if ($num < 2097152) {
      return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }

    return '';
  }

}
