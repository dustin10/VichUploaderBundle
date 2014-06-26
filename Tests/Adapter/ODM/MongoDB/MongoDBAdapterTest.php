<?php

namespace Vich\UploaderBundle\Tests\Adapter\ODM\MongoDB;

use Vich\UploaderBundle\Tests\DummyEntity;
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
        $entity = new DummyEntity();

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
