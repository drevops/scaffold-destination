<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Gettext;

use Drupal\Component\Gettext\PoHeader;
use Drupal\Component\Gettext\PoItem;
use Drupal\Component\Gettext\PoStreamWriter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Drupal\Component\Gettext\PoStreamWriter
 * @group Gettext
 */
class PoStreamWriterTest extends TestCase {

  use ProphecyTrait;

  /**
   * The PO writer object under test.
   *
   * @var \Drupal\Component\Gettext\PoStreamWriter
   */
  protected $poWriter;

  /**
   * The mock po file.
   *
   * @var \org\bovigo\vfs\vfsStreamFile
   */
  protected $poFile;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $poHeader = $this->prophesize(PoHeader::class);
    $poHeader->__toString()->willReturn('');
    $this->poWriter = new PoStreamWriter();
    $this->poWriter->setHeader($poHeader->reveal());

    $root = vfsStream::setup();
    $this->poFile = new vfsStreamFile('poWriter.po');
    $root->addChild($this->poFile);
  }

  /**
   * @covers ::getURI
   */
  public function testGetUriException(): void {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('No URI set.');

    $this->poWriter->getURI();
  }

  /**
   * @covers ::writeItem
   * @dataProvider providerWriteData
   */
  public function testWriteItem($poContent, $expected, $long): void {
    if ($long) {
      $this->expectException(\Exception::class);
      $this->expectExceptionMessage('Unable to write data:');
    }

    // Limit the file system quota to make the write fail on long strings.
    vfsStream::setQuota(10);

    $this->poWriter->setURI($this->poFile->url());
    $this->poWriter->open();

    $poItem = $this->prophesize(PoItem::class);
    $poItem->__toString()->willReturn($poContent);

    $this->poWriter->writeItem($poItem->reveal());
    $this->poWriter->close();
    $this->assertEquals(file_get_contents($this->poFile->url()), $expected);
  }

  /**
   * @return array
   *   - Content to write.
   *   - Written content.
   *   - Content longer than 10 bytes.
   */
  public static function providerWriteData() {
    // cSpell:disable
    return [
      ['', '', FALSE],
      ["\r\n", "\r\n", FALSE],
      ['write this if you can', 'write this', TRUE],
      ['éáíó>&', 'éáíó>&', FALSE],
      ['éáíó>&<', 'éáíó>&', TRUE],
      ['中文 890', '中文 890', FALSE],
      ['中文 89012', '中文 890', TRUE],
    ];
    // cSpell:enable
  }

  /**
   * @covers ::close
   */
  public function testCloseException(): void {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Cannot close stream that is not open.');

    $this->poWriter->close();
  }

}
