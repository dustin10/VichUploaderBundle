<?php

namespace Vich\UploaderBundle\Tests\EventListener\PropelGe;

use Vich\UploaderBundle\Tests\EventListener\Propel\ListenerTestCase as PropelListenerTestCase;

/**
 * Propel listener test case.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class ListenerTestCase extends PropelListenerTestCase
{
    /**
     * Creates a mock event.
     *
     * @return \Symfony\Component\EventDispatcher\GenericEvent The mock event.
     */
    protected function getEventMock()
    {
        return $this->getMockBuilder('\Glorpen\Propel\PropelBundle\Events\ModelEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
