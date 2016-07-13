<?php

namespace Vich\UploaderBundle\Tests\EventListener\PropelGe;

use Vich\UploaderBundle\EventListener\PropelGe\RemoveListener;

/**
 * Propel remove listener test case.
 * 
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class RemoveListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test
     */
    public function setUp()
    {
        parent::setUp();
    
        $this->listener = new RemoveListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }
    
    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('model.delete.post', $events);
    }
    
    /**
     * Test the onDelete method.
     */
    public function testOnDelete()
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
        ->method('remove')
        ->with($this->object, self::FIELD_NAME);
    
        $this->listener->onDelete($this->event);
    }
    
    /**
     * Test that onDelete skips non uploadable entity.
     */
    public function testOnDeleteSkipsNonUploadable()
    {
        $this->metadata
        ->expects($this->once())
        ->method('isUploadable')
        ->with('Vich\UploaderBundle\Tests\DummyEntity')
        ->will($this->returnValue(false));
    
        $this->handler
        ->expects($this->never())
        ->method('remove');
    
        $this->listener->onDelete($this->event);
    }
}
