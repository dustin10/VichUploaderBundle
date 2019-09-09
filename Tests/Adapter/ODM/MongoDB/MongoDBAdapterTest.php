<?php

namespace Vich\UploaderBundle\Tests\Adapter\ODM\MongoDB;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\ODM\MongoDB\MongoDBAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * MongoDBAdapterTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MongoDBAdapterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\class_exists(LifecycleEventArgs::class)) {
            self::markTestSkipped('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs does not exist.');
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
            ->method('getDocument')
            ->willReturn($entity);

        $adapter = new MongoDBAdapter();

        $this->assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
