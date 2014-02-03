<?php

namespace Vich\UploaderBundle\Tests\Adapter\ORM;

use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\DummyEntityProxyORM;

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
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
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

        $this->assertEquals($entity, $adapter->getObjectFromArgs($args));
    }

    /**
     * @dataProvider entityProvider
     */
    public function testGetClassName($entity, $expectedClassName)
    {
        $adapter = new DoctrineORMAdapter();

        $this->assertEquals($expectedClassName, $adapter->getClassName($entity));
    }

    public function entityProvider()
    {
        $classicEntity = new DummyEntity();
        $proxiedEntity = new DummyEntityProxyORM();

        return array(
            array($classicEntity, 'Vich\UploaderBundle\Tests\DummyEntity'),
            array($proxiedEntity, 'Vich\UploaderBundle\Tests\DummyEntity'),
        );
    }
}
