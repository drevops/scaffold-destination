<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel\Views;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\user\Entity\Role;
use Drupal\views\Entity\View;
use Drupal\views\Views;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Tests the roles filter handler.
 *
 * @group user
 *
 * @see \Drupal\user\Plugin\views\filter\Roles
 */
class HandlerFilterRolesTest extends UserKernelTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_user_name'];

  /**
   * Tests that role filter dependencies are calculated correctly.
   */
  public function testDependencies(): void {
    $role = Role::create(['id' => 'test_user_role', 'label' => 'Test user role']);
    $role->save();
    $view = View::load('test_user_name');
    $expected = [
      'module' => ['user'],
    ];
    $this->assertEquals($expected, $view->getDependencies());

    $display = &$view->getDisplay('default');
    $display['display_options']['filters']['roles_target_id'] = [
      'id' => 'roles_target_id',
      'table' => 'user__roles',
      'field' => 'roles_target_id',
      'value' => ['test_user_role' => 'test_user_role'],
      'plugin_id' => 'user_roles',
    ];
    $view->save();
    $expected['config'][] = 'user.role.test_user_role';
    $this->assertEquals($expected, $view->getDependencies());

    $view = View::load('test_user_name');
    $display = &$view->getDisplay('default');
    $display['display_options']['filters']['roles_target_id'] = [
      'id' => 'roles_target_id',
      'table' => 'user__roles',
      'field' => 'roles_target_id',
      'value' => [
        'test_user_role' => 'test_user_role',
      ],
      'operator' => 'empty',
      'plugin_id' => 'user_roles',
    ];
    $view->save();
    unset($expected['config']);
    $this->assertEquals($expected, $view->getDependencies());

    $view = View::load('test_user_name');
    $display = &$view->getDisplay('default');
    $display['display_options']['filters']['roles_target_id'] = [
      'id' => 'roles_target_id',
      'table' => 'user__roles',
      'field' => 'roles_target_id',
      'value' => [
        'test_user_role' => 'test_user_role',
      ],
      'operator' => 'not empty',
      'plugin_id' => 'user_roles',
    ];
    $view->save();
    $this->assertEquals($expected, $view->getDependencies());

    $view = Views::getView('test_user_name');
    $view->initDisplay();
    $view->initHandlers();
    $this->assertEquals(['test_user_role'], array_keys($view->filter['roles_target_id']->getValueOptions()));

    $view = View::load('test_user_name');
    $display = &$view->getDisplay('default');
    $display['display_options']['filters']['roles_target_id'] = [
      'id' => 'roles_target_id',
      'table' => 'user__roles',
      'field' => 'roles_target_id',
      'value' => [],
      'plugin_id' => 'user_roles',
    ];
    $view->save();
    $this->assertEquals($expected, $view->getDependencies());
  }

  /**
   * Tests that a warning is triggered if the filter references a missing role.
   */
  public function testMissingRole(): void {
    $logger = $this->prophesize(LoggerInterface::class);
    $this->container->get('logger.factory')
      ->get('system')
      ->addLogger($logger->reveal());

    $role = Role::create(['id' => 'test_user_role', 'label' => 'Test user role']);
    $role->save();
    /** @var \Drupal\views\Entity\View $view */
    $view = View::load('test_user_name');
    $display = &$view->getDisplay('default');
    $display['display_options']['filters']['roles_target_id'] = [
      'id' => 'roles_target_id',
      'table' => 'user__roles',
      'field' => 'roles_target_id',
      'value' => ['test_user_role' => 'test_user_role'],
      'plugin_id' => 'user_roles',
    ];
    // Ensure no warning is triggered before the role is deleted.
    $view->calculateDependencies();
    $role->delete();

    // Recalculate after role deletion.
    $logger->log(
      RfcLogLevel::WARNING,
      'View %view depends on role %role, but the role does not exist.',
      Argument::allOf(
        Argument::withEntry('%view', 'test_user_name'),
        Argument::withEntry('%role', 'test_user_role'),
      )
    )->shouldBeCalled();
    $view->calculateDependencies();
  }

}
