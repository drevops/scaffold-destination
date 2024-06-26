<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests the node entity preview functionality for anonymous user.
 *
 * @group node
 */
class NodePreviewAnonymousTest extends BrowserTestBase {

  /**
   * Enable node module to test on the preview.
   *
   * @var array
   */
  protected static $modules = ['node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Create Basic page node type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);

    // Grant create and editing permissions to anonymous user:
    $anonymous_role = Role::load(AccountInterface::ANONYMOUS_ROLE);
    $anonymous_role->grantPermission('create page content');
    $anonymous_role->save();
  }

  /**
   * Checks the node preview functionality for anonymous users.
   */
  public function testAnonymousPagePreview(): void {

    $title_key = 'title[0][value]';
    $body_key = 'body[0][value]';

    // Fill in node creation form and preview node.
    $edit = [
      $title_key => $this->randomMachineName(),
      $body_key => $this->randomMachineName(),
    ];
    $this->drupalGet('node/add/page');
    $this->submitForm($edit, 'Preview');

    // Check that the preview is displaying the title, body and term.
    $this->assertSession()->linkExists('Back to content editing');
    $this->assertSession()->responseContains($edit[$body_key]);
    $this->assertSession()->titleEquals($edit[$title_key] . ' | Drupal');
  }

}
