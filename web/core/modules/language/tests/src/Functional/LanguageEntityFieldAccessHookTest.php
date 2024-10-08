<?php

declare(strict_types=1);

namespace Drupal\Tests\language\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests language picker compatibility with hook_entity_field_access.
 *
 * @group language
 */
class LanguageEntityFieldAccessHookTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'text',
    'field',
    'filter',
    'language',
    'language_entity_field_access_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests compatibility with hook_entity_field_access().
   */
  public function testHookEntityFieldAccess(): void {
    // Create an admin user and do the login.
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    // Assess the field is not visible.
    $this->drupalGet('node/add/page');
    $this->assertSession()->fieldNotExists('langcode[0][value]');

    $this->drupalLogout();
  }

}
