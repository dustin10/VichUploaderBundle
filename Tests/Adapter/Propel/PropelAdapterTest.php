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
    /**
     * Test the getObjectFromEvent method.
     */
    public function testGetObjectFromEvent()
    {
        $event = $this->getMock('\Symfony\Component\EventDispatcher\GenericEvent');
        $event
            ->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue(42));

        $adapter = new PropelAdapter();
        $this->assertSame(42, $adapter->getObjectFromEvent($event));
    }

    /**
     * Tests the getReflectionClass method.
     */
    public function testGetReflectionClass()
    {
        $adapter = new PropelAdapter();

        $obj = new \DateTime();
        $class = $adapter->getReflectionClass($obj);

        $this->assertSame('DateTime', $class->getName());
    }
}
