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
    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
    {
        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            $this->markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        } else {
            $entity = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

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

    /**
     * Tests the getReflectionClass method.
     */
    public function testGetReflectionClass()
    {
        if (!interface_exists('Doctrine\ORM\Proxy\Proxy')) {
            $this->markTestSkipped('Doctrine\ORM\Proxy\Proxy does not exist.');
        } else {
            $obj = new DummyEntity();
            $adapter = new DoctrineORMAdapter();
            $class = $adapter->getReflectionClass($obj);

            $this->assertEquals($class->getName(), get_class($obj));
        }
    }

    /**
     * Tests the getReflectionClass method with a proxy.
     */
    public function testGetReflectionClassProxy()
    {
        if (!interface_exists('Doctrine\ORM\Proxy\Proxy')) {
            $this->markTestSkipped('Doctrine\ORM\Proxy\Proxy does not exist.');
        } else {
            $obj = new DummyEntityProxyORM();
            $adapter = new DoctrineORMAdapter();
            $class = $adapter->getReflectionClass($obj);

            $this->assertEquals($class->getName(), get_parent_class($obj));
        }
    }
}
