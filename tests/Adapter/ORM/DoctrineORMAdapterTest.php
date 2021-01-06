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
final class DoctrineORMAdapterTest extends TestCase
{
    /**
     * @requires function LifecycleEventArgs::getEntity
     */
    public function testGetObjectFromArgs(): void
    {
        $entity = new DummyEntity();

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(self::once())
            ->method('getEntity')
            ->willReturn($entity);

        $adapter = new DoctrineORMAdapter();

        self::assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
