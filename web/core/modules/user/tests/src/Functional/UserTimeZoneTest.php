<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Functional;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Set a user time zone and verify that dates are displayed in local time.
 *
 * @group user
 */
class UserTimeZoneTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'system_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the display of dates and time when user-configurable time zones are set.
   */
  public function testUserTimeZone(): void {
    // Setup date/time settings for Los Angeles time.
    $this->config('system.date')
      ->set('timezone.user.configurable', 1)
      ->set('timezone.default', 'America/Los_Angeles')
      ->save();

    // Load the 'medium' date format, which is the default for node creation
    // time, and override it. Since we are testing time zones with Daylight
    // Saving Time, and need to future proof against changes to the zoneinfo
    // database, we choose the 'I' format placeholder instead of a
    // human-readable zone name. With 'I', a 1 means the date is in DST, and 0
    // if not.
    DateFormat::load('medium')
      ->setPattern('Y-m-d H:i I')
      ->save();

    // Create a user account and login.
    $web_user = $this->drupalCreateUser();
    $this->drupalLogin($web_user);

    // Create some nodes with different authored-on dates.
    // Two dates in PST (winter time):
    $date1 = '2007-03-09 21:00:00 -0800';
    $date2 = '2007-03-11 01:00:00 -0800';
    // One date in PDT (summer time):
    $date3 = '2007-03-20 21:00:00 -0700';
    $this->drupalCreateContentType(['type' => 'article']);
    $node1 = $this->drupalCreateNode(['created' => strtotime($date1), 'type' => 'article']);
    $node2 = $this->drupalCreateNode(['created' => strtotime($date2), 'type' => 'article']);
    $node3 = $this->drupalCreateNode(['created' => strtotime($date3), 'type' => 'article']);

    // Confirm date format and time zone.
    $this->drupalGet('node/' . $node1->id());
    // Date should be PST.
    $this->assertSession()->pageTextContains('2007-03-09 21:00 0');
    $this->drupalGet('node/' . $node2->id());
    // Date should be PST.
    $this->assertSession()->pageTextContains('2007-03-11 01:00 0');
    $this->drupalGet('node/' . $node3->id());
    // Date should be PST.
    $this->assertSession()->pageTextContains('2007-03-20 21:00 1');

    // Change user time zone to Santiago time.
    $edit = [];
    $edit['mail'] = $web_user->getEmail();
    $edit['timezone'] = 'America/Santiago';
    $this->drupalGet("user/" . $web_user->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');

    // Confirm date format and time zone.
    $this->drupalGet('node/' . $node1->id());
    // Date should be Chile summer time, five hours ahead of PST.
    $this->assertSession()->pageTextContains('2007-03-10 02:00 1');
    $this->drupalGet('node/' . $node2->id());
    // Date should be Chile time, four hours ahead of PST.
    $this->assertSession()->pageTextContains('2007-03-11 05:00 0');
    $this->drupalGet('node/' . $node3->id());
    // Date should be Chile time, three hours ahead of PDT.
    $this->assertSession()->pageTextContains('2007-03-21 00:00 0');

    // Ensure that anonymous users also use the default timezone.
    $this->drupalLogout();
    $this->drupalGet('node/' . $node1->id());
    // Date should be PST.
    $this->assertSession()->pageTextContains('2007-03-09 21:00 0');
    $this->drupalGet('node/' . $node2->id());
    // Date should be PST.
    $this->assertSession()->pageTextContains('2007-03-11 01:00 0');
    $this->drupalGet('node/' . $node3->id());
    // Date should be PDT.
    $this->assertSession()->pageTextContains('2007-03-20 21:00 1');

    // Format a date without accessing the current user at all and
    // ensure that it uses the default timezone.
    $this->drupalGet('/system-test/date');
    // Date should be PST.
    $this->assertSession()->pageTextContains('2016-01-13 08:29 0');
  }

}
