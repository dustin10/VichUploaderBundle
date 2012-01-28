<?php

namespace Vich\UploaderBundle\Tests\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyEntityProxyMongo;
use Vich\UploaderBundle\Adapter\ODM\MongoDB\MongoDBAdapter;

/**
 * MongoDBAdapterTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class MongoDBAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
    {
        if (!class_exists('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')) {
            $this->markTestSkipped('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs does not exist.');
        } else {
            $entity = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

            $args = $this->getMockBuilder('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')
                    ->disableOriginalConstructor()
                    ->getMock();
            $args
                ->expects($this->once())
                ->method('getDocument')
                ->will($this->returnValue($entity));

            $adapter = new MongoDBAdapter();

            $this->assertEquals($entity, $adapter->getObjectFromArgs($args));
        }
    }

    /**
     * Tests the getReflectionClass method.
     */
    public function testGetReflectionClass()
    {
        if (!interface_exists('Doctrine\ODM\MongoDB\Proxy\Proxy')) {
            $this->markTestSkipped('Doctrine\ODM\MongoDB\Proxy\Proxy does not exist.');
        } else {
            $obj = new DummyEntity();
            $adapter = new MongoDBAdapter();
            $class = $adapter->getReflectionClass($obj);

            $this->assertEquals($class->getName(), get_class($obj));
        }
    }

    /**
     * Tests the getReflectionClass method with a proxy.
     */
    public function testGetReflectionClassProxy()
    {
        if (!interface_exists('Doctrine\ODM\MongoDB\Proxy\Proxy')) {
            $this->markTestSkipped('Doctrine\ODM\MongoDB\Proxy\Proxy does not exist.');
        } else {
            $obj = new DummyEntityProxyMongo();
            $adapter = new MongoDBAdapter();
            $class = $adapter->getReflectionClass($obj);

            $this->assertEquals($class->getName(), get_parent_class($obj));
        }
    }
}
