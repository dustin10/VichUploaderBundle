<?php

namespace Vich\UploaderBundle\Tests\Adapter\ORM;

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
    public static function setUpBeforeClass()
    {
        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            self::markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        }
    }

    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
    {
        $entity = new DummyEntity();

        $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $adapter = new DoctrineORMAdapter();

        $this->assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
