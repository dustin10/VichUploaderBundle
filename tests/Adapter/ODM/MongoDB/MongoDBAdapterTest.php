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
final class MongoDBAdapterTest extends TestCase
{
    /**
     * @requires function LifecycleEventArgs::getDocument
     */
    public function testGetObjectFromArgs(): void
    {
        $entity = new DummyEntity();

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(self::once())
            ->method('getDocument')
            ->willReturn($entity);

        $adapter = new MongoDBAdapter();

        self::assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
