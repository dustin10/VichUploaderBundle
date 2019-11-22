<?php

namespace Vich\UploaderBundle\Tests\Adapter\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * DoctrineORMAdapterTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineORMAdapterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\class_exists(LifecycleEventArgs::class)) {
            self::markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        }
    }

    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs(): void
    {
        $entity = new DummyEntity();

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);

        $adapter = new DoctrineORMAdapter();

        $this->assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
