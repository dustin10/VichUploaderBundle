<?php

namespace Vich\UploaderBundle\Tests\Adapter\PHPCR;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\PHPCR\PHPCRAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 */
class PHPCRAdapterTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!class_exists(LifecycleEventArgs::class)) {
            self::markTestSkipped('Doctrine\Common\Persistence\Event\LifecycleEventArgs does not exist.');
        }
    }

    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
    {
        $entity = new DummyEntity();

        $args = $this->createMock(LifecycleEventArgs::class);
        $args
            ->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($entity));

        $adapter = new PHPCRAdapter();

        $this->assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
