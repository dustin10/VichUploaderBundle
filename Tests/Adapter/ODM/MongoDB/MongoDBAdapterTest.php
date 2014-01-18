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
    public static function setUpBeforeClass()
    {
        if (!class_exists('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs')) {
            self::markTestSkipped('Doctrine\ODM\MongoDB\Event\LifecycleEventArgs does not exist.');
        }
    }

    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
    {
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

    /**
     * @dataProvider entityProvider
     */
    public function testGetClassName($entity, $expectedClassName)
    {
        $adapter = new MongoDBAdapter();

        $this->assertEquals($expectedClassName, $adapter->getClassName($entity));
    }

    public function entityProvider()
    {
        $classicEntity = new DummyEntity();
        $proxiedEntity = new DummyEntityProxyMongo();

        return array(
            array($classicEntity, 'Vich\UploaderBundle\Tests\DummyEntity'),
            array($proxiedEntity, 'Vich\UploaderBundle\Tests\DummyEntity'),
        );
    }
}
