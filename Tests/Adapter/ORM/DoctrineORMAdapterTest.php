<?php

namespace Vich\UploaderBundle\Tests\Adapter\ORM;

use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyEntityProxyORM;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;

/**
 * DoctrineORMAdapterTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DoctrineORMAdapterTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            self::markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        }
    }

    /**
     * Test the getObjectFromEvent method.
     */
    public function testGetObjectFromEvent()
    {
        $entity = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
                ->disableOriginalConstructor()
                ->getMock();
        $args
            ->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $adapter = new DoctrineORMAdapter();

        $this->assertEquals($entity, $adapter->getObjectFromEvent($args));
    }

    /**
     * Tests the getReflectionClass method.
     */
    public function testGetClassName()
    {
        $obj = new DummyEntity();
        $adapter = new DoctrineORMAdapter();
        $class = $adapter->getClassName($obj);

        $this->assertEquals('Vich\UploaderBundle\Tests\DummyEntity', $class);
    }

    /**
     * Tests the getReflectionClass method with a proxy.
     */
    public function testGetClassNameWithProxy()
    {
        $obj = new DummyEntityProxyORM();
        $adapter = new DoctrineORMAdapter();
        $class = $adapter->getClassName($obj);

        $this->assertEquals('Vich\UploaderBundle\Tests\DummyEntity', $class);
    }
}
