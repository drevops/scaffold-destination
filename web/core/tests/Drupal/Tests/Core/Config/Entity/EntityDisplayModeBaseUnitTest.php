<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Config\Entity;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityDisplayModeBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Entity\EntityDisplayModeBase
 * @group Config
 */
class EntityDisplayModeBaseUnitTest extends UnitTestCase {

  /**
   * The entity under test.
   *
   * @var \Drupal\Core\Entity\EntityDisplayModeBase|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entity;

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityInfo;

  /**
   * The entity type manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The ID of the type of the entity under test.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The UUID generator used for testing.
   *
   * @var \Drupal\Component\Uuid\UuidInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $uuid;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityType = $this->randomMachineName();

    $this->entityInfo = $this->createMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->entityInfo->expects($this->any())
      ->method('getProvider')
      ->willReturn('entity');

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    $this->uuid = $this->createMock('\Drupal\Component\Uuid\UuidInterface');

    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('uuid', $this->uuid);

    \Drupal::setContainer($container);
  }

  /**
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies(): void {
    $target_entity_type_id = $this->randomMachineName(16);

    $target_entity_type = $this->createMock('\Drupal\Core\Entity\EntityTypeInterface');
    $target_entity_type->expects($this->any())
      ->method('getProvider')
      ->willReturn('test_module');
    $values = ['targetEntityType' => $target_entity_type_id];

    $this->entityTypeManager->expects($this->exactly(2))
      ->method('getDefinition')
      ->willReturnMap([
        [$target_entity_type_id, TRUE, $target_entity_type],
        [$this->entityType, TRUE, $this->entityInfo],
      ]);

    $this->entity = new EntityDisplayModeBaseTestableClass($values, $this->entityType);
    $dependencies = $this->entity->calculateDependencies()->getDependencies();
    $this->assertContains('test_module', $dependencies['module']);
  }

  /**
   * @covers ::setTargetType
   */
  public function testSetTargetType(): void {
    // Generate mock.
    $mock = $this->getMockBuilder('Drupal\Core\Entity\EntityDisplayModeBase')
      ->onlyMethods([])
      ->setConstructorArgs([['something' => 'nothing'], 'test_type'])
      ->getMock();

    // Some test values.
    $bad_target = 'uninitialized';
    $target = 'test_target_type';

    // Gain access to the protected property.
    $property = new \ReflectionProperty($mock, 'targetEntityType');
    // Set the property to a known state.
    $property->setValue($mock, $bad_target);

    // Set the target type.
    $mock->setTargetType($target);

    // Test the outcome.
    $this->assertNotEquals($bad_target, $property->getValue($mock));
    $this->assertEquals($target, $property->getValue($mock));
  }

  /**
   * @covers ::getTargetType
   */
  public function testGetTargetType(): void {
    // Generate mock.
    $mock = $this->getMockBuilder('Drupal\Core\Entity\EntityDisplayModeBase')
      ->onlyMethods([])
      ->setConstructorArgs([['something' => 'nothing'], 'test_type'])
      ->getMock();

    // A test value.
    $target = 'test_target_type';

    // Gain access to the protected property.
    $property = new \ReflectionProperty($mock, 'targetEntityType');
    // Set the property to a known state.
    $property->setValue($mock, $target);

    // Get the target type.
    $value = $mock->getTargetType($target);

    // Test the outcome.
    $this->assertEquals($value, $property->getValue($mock));
  }

}

/**
 * A class extending EntityDisplayModeBase for testing purposes.
 */
class EntityDisplayModeBaseTestableClass extends EntityDisplayModeBase {
}
