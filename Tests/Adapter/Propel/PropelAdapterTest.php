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

    public function testGetClassName()
    {
        $adapter = new PropelAdapter();

        $obj = new \DateTime();
        $class = $adapter->getClassName($obj);

        $this->assertSame('DateTime', $class);
    }
}
