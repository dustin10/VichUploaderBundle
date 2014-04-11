<?php

namespace Vich\UploaderBundle\Tests\Adapter\Propel;

use Vich\UploaderBundle\Adapter\Propel\PropelORMAdapter;

/**
 * PropelORMAdapterTest
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelORMAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    public static function setUpBeforeClass()
    {
        if (!class_exists('Symfony\Component\EventDispatcher\GenericEvent')) {
            self::markTestSkipped('Symfony\Component\EventDispatcher\GenericEvent does not exist.');
        }
    }

    public function setUp()
    {
        $this->adapter = new PropelORMAdapter();
    }

    public function testGetObjectFromArgs()
    {
        $event = $this->getMock('\Symfony\Component\EventDispatcher\GenericEvent');
        $event
            ->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue(42));

        $this->assertSame(42, $this->adapter->getObjectFromArgs($event));
    }

    public function testGetClassName()
    {
        $this->assertSame('DateTime', $this->adapter->getClassName(new \DateTime()));
    }

    public function testRecomputeChangeset()
    {
        $event = $this->getMock('\Symfony\Component\EventDispatcher\GenericEvent');

        // does nothing but should be callable
        $this->adapter->recomputeChangeSet($event);
    }
}
