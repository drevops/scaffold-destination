<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ChainRequestPolicy;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\Core\PageCache\ChainRequestPolicy
 * @group PageCache
 */
class ChainRequestPolicyTest extends UnitTestCase {

  /**
   * The chain request policy under test.
   *
   * @var \Drupal\Core\PageCache\ChainRequestPolicy
   */
  protected $policy;

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->policy = new ChainRequestPolicy();
    $this->request = new Request();
  }

  /**
   * Asserts that check() returns NULL if the chain is empty.
   *
   * @covers ::check
   */
  public function testEmptyChain(): void {
    $result = $this->policy->check($this->request);
    $this->assertNull($result);
  }

  /**
   * Asserts that check() returns NULL if a rule returns NULL.
   *
   * @covers ::check
   */
  public function testNullRuleChain(): void {
    $rule = $this->createMock('Drupal\Core\PageCache\RequestPolicyInterface');
    $rule->expects($this->once())
      ->method('check')
      ->with($this->request)
      ->willReturn(NULL);

    $this->policy->addPolicy($rule);

    $result = $this->policy->check($this->request);
    $this->assertNull($result);
  }

  /**
   * Asserts that check() throws an exception if a rule returns an invalid value.
   *
   * @dataProvider providerChainExceptionOnInvalidReturnValue
   * @covers ::check
   */
  public function testChainExceptionOnInvalidReturnValue($return_value): void {
    $rule = $this->createMock('Drupal\Core\PageCache\RequestPolicyInterface');
    $rule->expects($this->once())
      ->method('check')
      ->with($this->request)
      ->willReturn($return_value);

    $this->policy->addPolicy($rule);

    $this->expectException(\UnexpectedValueException::class);
    $this->policy->check($this->request);
  }

  /**
   * Provides test data for testChainExceptionOnInvalidReturnValue.
   *
   * @return array
   *   Test input and expected result.
   */
  public static function providerChainExceptionOnInvalidReturnValue() {
    return [
      [FALSE],
      [0],
      [1],
      [TRUE],
      [[1, 2, 3]],
      [new \stdClass()],
    ];
  }

  /**
   * Asserts that check() returns ALLOW if any of the rules returns ALLOW.
   *
   * @dataProvider providerAllowIfAnyRuleReturnedAllow
   * @covers ::check
   */
  public function testAllowIfAnyRuleReturnedAllow($return_values): void {
    foreach ($return_values as $return_value) {
      $rule = $this->createMock('Drupal\Core\PageCache\RequestPolicyInterface');
      $rule->expects($this->once())
        ->method('check')
        ->with($this->request)
        ->willReturn($return_value);

      $this->policy->addPolicy($rule);
    }

    $actual_result = $this->policy->check($this->request);
    $this->assertSame(RequestPolicyInterface::ALLOW, $actual_result);
  }

  /**
   * Provides test data for testAllowIfAnyRuleReturnedAllow.
   *
   * @return array
   *   Test input and expected result.
   */
  public static function providerAllowIfAnyRuleReturnedAllow() {
    return [
      [[RequestPolicyInterface::ALLOW]],
      [[NULL, RequestPolicyInterface::ALLOW]],
    ];
  }

  /**
   * Asserts that check() returns immediately when a rule returned DENY.
   */
  public function testStopChainOnFirstDeny(): void {
    $rule1 = $this->createMock('Drupal\Core\PageCache\RequestPolicyInterface');
    $rule1->expects($this->once())
      ->method('check')
      ->with($this->request)
      ->willReturn(RequestPolicyInterface::ALLOW);
    $this->policy->addPolicy($rule1);

    $deny_rule = $this->createMock('Drupal\Core\PageCache\RequestPolicyInterface');
    $deny_rule->expects($this->once())
      ->method('check')
      ->with($this->request)
      ->willReturn(RequestPolicyInterface::DENY);
    $this->policy->addPolicy($deny_rule);

    $ignored_rule = $this->createMock('Drupal\Core\PageCache\RequestPolicyInterface');
    $ignored_rule->expects($this->never())
      ->method('check');
    $this->policy->addPolicy($ignored_rule);

    $actual_result = $this->policy->check($this->request);
    $this->assertSame(RequestPolicyInterface::DENY, $actual_result);
  }

}
