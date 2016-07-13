<?php

namespace Vich\UploaderBundle\Tests\EventListener\PropelGe;

use Vich\UploaderBundle\EventListener\PropelGe\InjectListener;

/**
 * Propel remove listener test case.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class InjectListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test
     */
    public function setUp()
    {
        parent::setUp();
    
        $this->listener = new InjectListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }
    
    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('model.hydrate.post', $events);
    }
    

    /**
     * Test the onHydrate method.
     */
    public function testOnHydrate()
    {
        $this->metadata
        ->expects($this->once())
        ->method('isUploadable')
        ->with('Vich\UploaderBundle\Tests\DummyEntity')
        ->will($this->returnValue(true));
    
        $this->metadata
        ->expects($this->once())
        ->method('getUploadableFields')
        ->with('Vich\UploaderBundle\Tests\DummyEntity', self::MAPPING_NAME)
        ->will($this->returnValue(array(
            array('propertyName' => self::FIELD_NAME)
        )));
    
        $this->handler
        ->expects($this->once())
        ->method('inject')
        ->with($this->object, self::FIELD_NAME);
    
        $this->listener->onHydrate($this->event);
    }
    
    /**
     * Test that onHydrate skips non uploadable entity.
     */
    public function testOnHydrateSkipsNonUploadable()
    {
        $this->metadata
        ->expects($this->once())
        ->method('isUploadable')
        ->with('Vich\UploaderBundle\Tests\DummyEntity')
        ->will($this->returnValue(false));
    
        $this->handler
        ->expects($this->never())
        ->method('inject', self::MAPPING_NAME);
    
        $this->listener->onHydrate($this->event);
    }
}
