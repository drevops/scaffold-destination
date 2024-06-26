<?php

declare(strict_types=1);

namespace Drupal\Tests\help\Functional;

// cspell:ignore hilfetestmodul testen übersetzung

/**
 * Verifies help topic translations.
 *
 * @group help
 */
class HelpTopicTranslationTest extends HelpTopicTranslatedTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create user and log in.
    $this->drupalLogin($this->createUser([
      'access help pages',
      'view the administration theme',
      'administer permissions',
    ]));
  }

  /**
   * Tests help topic translations.
   */
  public function testHelpTopicTranslations(): void {
    $session = $this->assertSession();

    // Verify that help topic link is translated on admin/help.
    $this->drupalGet('admin/help');
    $session->linkExists('ABC-Hilfetestmodul');
    // Verify that the language cache tag appears on admin/help.
    $session->responseHeaderContains('X-Drupal-Cache-Contexts', 'languages:language_interface');
    // Verify that help topic is translated.
    $this->drupalGet('admin/help/topic/help_topics_test.test');
    $session->pageTextContains('ABC-Hilfetestmodul');
    $session->pageTextContains('Übersetzung testen.');
    // Verify that the language cache tag appears on a topic page.
    $session->responseHeaderContains('X-Drupal-Cache-Contexts', 'languages:language_interface');
  }

}
