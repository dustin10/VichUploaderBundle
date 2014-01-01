<?php

namespace Vich\UploaderBundle\Tests\Adapter\Propel;

use Vich\UploaderBundle\Adapter\Propel\PropelAdapter;

/**
 * Propel adapter tests.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new PropelAdapter();
    }

    public function testGetObjectFromEvent()
    {
        $event = $this->getMock('\Symfony\Component\EventDispatcher\GenericEvent');
        $event
            ->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue(42));

        $this->assertSame(42, $this->adapter->getObjectFromEvent($event));
    }

    public function testGetClassName()
    {
        $obj = new \DateTime();

        $this->assertSame('DateTime', $this->adapter->getClassName($obj));
    }

    public function testRecomputeChangeset()
    {
        $event = $this->getMock('\Symfony\Component\EventDispatcher\GenericEvent');

        // does nothing but should be callable
        $this->adapter->recomputeChangeSet($event);
    }
}
