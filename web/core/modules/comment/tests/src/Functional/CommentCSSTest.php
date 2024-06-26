<?php

declare(strict_types=1);

namespace Drupal\Tests\comment\Functional;

use Drupal\Core\Language\LanguageInterface;
use Drupal\comment\CommentInterface;
use Drupal\user\RoleInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;

/**
 * Tests CSS classes on comments.
 *
 * @group comment
 */
class CommentCSSTest extends CommentTestBase {

  use GeneratePermutationsTrait;

  /**
   * The theme to install as the default for testing.
   *
   * @var string
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Allow anonymous users to see comments.
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, [
      'access comments',
      'access content',
    ]);
  }

  /**
   * Tests CSS classes on comments.
   */
  public function testCommentClasses(): void {
    // Create all permutations for comments, users, and nodes.
    $parameters = [
      'node_uid' => [0, $this->webUser->id()],
      'comment_uid' => [0, $this->webUser->id(), $this->adminUser->id()],
      'comment_status' => [CommentInterface::PUBLISHED, CommentInterface::NOT_PUBLISHED],
      'user' => ['anonymous', 'authenticated', 'admin'],
    ];
    $permutations = $this->generatePermutations($parameters);

    foreach ($permutations as $case) {
      // Create a new node.
      $node = $this->drupalCreateNode(['type' => 'article', 'uid' => $case['node_uid']]);

      // Add a comment.
      /** @var \Drupal\comment\CommentInterface $comment */
      $comment = Comment::create([
        'entity_id' => $node->id(),
        'entity_type' => 'node',
        'field_name' => 'comment',
        'uid' => $case['comment_uid'],
        'status' => $case['comment_status'],
        'subject' => $this->randomMachineName(),
        'language' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
        'comment_body' => [LanguageInterface::LANGCODE_NOT_SPECIFIED => [$this->randomMachineName()]],
      ]);
      $comment->save();

      // Adjust the current/viewing user.
      switch ($case['user']) {
        case 'anonymous':
          if ($this->loggedInUser) {
            $this->drupalLogout();
          }
          $case['user_uid'] = 0;
          break;

        case 'authenticated':
          $this->drupalLogin($this->webUser);
          $case['user_uid'] = $this->webUser->id();
          break;

        case 'admin':
          $this->drupalLogin($this->adminUser);
          $case['user_uid'] = $this->adminUser->id();
          break;
      }
      // Request the node with the comment.
      $this->drupalGet('node/' . $node->id());
      $settings = $this->getDrupalSettings();

      // Verify the data-history-node-id attribute, which is necessary for the
      // by-viewer class and the "new" indicator, see below.
      $this->assertSession()->elementsCount('xpath', '//*[@data-history-node-id="' . $node->id() . '"]', 1);

      // Verify classes if the comment is visible for the current user.
      if ($case['comment_status'] == CommentInterface::PUBLISHED || $case['user'] == 'admin') {
        // Verify the by-anonymous class.
        $comments = '//*[contains(@class, "comment") and contains(@class, "by-anonymous")]';
        if ($case['comment_uid'] == 0) {
          $this->assertSession()->elementsCount('xpath', $comments, 1);
        }
        else {
          $this->assertSession()->elementNotExists('xpath', $comments);
        }

        // Verify the by-node-author class.
        $comments = '//*[contains(@class, "comment") and contains(@class, "by-node-author")]';
        if ($case['comment_uid'] > 0 && $case['comment_uid'] == $case['node_uid']) {
          $this->assertSession()->elementsCount('xpath', $comments, 1);
        }
        else {
          $this->assertSession()->elementNotExists('xpath', $comments);
        }

        // Verify the data-comment-user-id attribute, which is used by the
        // drupal.comment-by-viewer library to add a by-viewer when the current
        // user (the viewer) was the author of the comment. We do this in Java-
        // Script to prevent breaking the render cache.
        $this->assertSession()->elementsCount('xpath', '//*[contains(@class, "comment") and @data-comment-user-id="' . $case['comment_uid'] . '"]', 1);
        $this->assertSession()->responseContains($this->getModulePath('comment') . '/js/comment-by-viewer.js');
      }

      // Verify the unpublished class.
      $comments = '//*[contains(@class, "comment") and contains(@class, "unpublished")]';
      if ($case['comment_status'] == CommentInterface::NOT_PUBLISHED && $case['user'] == 'admin') {
        $this->assertSession()->elementsCount('xpath', $comments, 1);
      }
      else {
        $this->assertSession()->elementNotExists('xpath', $comments);
      }

      // Verify the data-comment-timestamp attribute, which is used by the
      // drupal.comment-new-indicator library to add a "new" indicator to each
      // comment that was created or changed after the last time the current
      // user read the corresponding node.
      if ($case['comment_status'] == CommentInterface::PUBLISHED || $case['user'] == 'admin') {
        $this->assertSession()->elementsCount('xpath', '//*[contains(@class, "comment")]/*[@data-comment-timestamp="' . $comment->getChangedTime() . '"]', 1);
        $expectedJS = ($case['user'] !== 'anonymous');
        $this->assertSame($expectedJS, isset($settings['ajaxPageState']['libraries']) && in_array('comment/drupal.comment-new-indicator', explode(',', $settings['ajaxPageState']['libraries'])), 'drupal.comment-new-indicator library is present.');
      }
    }
  }

}
