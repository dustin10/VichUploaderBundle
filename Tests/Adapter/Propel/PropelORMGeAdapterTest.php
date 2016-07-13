<?php

namespace Vich\UploaderBundle\Tests\Adapter\Propel;

use Vich\UploaderBundle\Adapter\Propel\PropelORMGaAdapter;

/**
 * PropelORMGeAdapterTest
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class PropelORMGeAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    public static function setUpBeforeClass()
    {
        if (!class_exists('Glorpen\PropelBundle\Events\ModelEvent')) {
            self::markTestSkipped('Glorpen\PropelBundle\Events\ModelEvent does not exist.');
        }
    }

    public function setUp()
    {
        $this->adapter = new PropelORMGaAdapter();
    }

    public function testGetObjectFromArgs()
    {
        $event = $this->getMock('\Glorpen\PropelBundle\Events\ModelEvent');
        $event
            ->expects($this->once())
            ->method('getModel')
            ->will($this->returnValue(42));

        $this->assertSame(42, $this->adapter->getObjectFromArgs($event));
    }

    public function testRecomputeChangeset()
    {
        $event = $this->getMock('\Glorpen\PropelBundle\Events\ModelEvent');

        // does nothing but should be callable
        $this->adapter->recomputeChangeSet($event);
    }
}
