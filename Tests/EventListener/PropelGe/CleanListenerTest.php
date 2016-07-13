<?php

namespace Vich\UploaderBundle\Tests\EventListener\PropelGe;

use Vich\UploaderBundle\EventListener\PropelGe\CleanListener;

/**
 * Propel clean listener test case.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class CleanListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test
     */
    public function setUp()
    {
        parent::setUp();
    
        $this->listener = new CleanListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }
    
    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('model.update.pre', $events);
    }
    

    /**
     * Test the onUpload method.
     */
    public function testOnUpload()
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
        ->method('clean')
        ->with($this->object, self::FIELD_NAME);
    
        $this->adapter
        ->expects($this->never())
        ->method('recomputeChangeSet')
        ->with($this->event);
    
        $this->listener->onUpload($this->event);
    }
    
    /**
     * Test that onUpload skips non uploadable entity.
     */
    public function testOnUploadSkipsNonUploadable()
    {
        $this->metadata
        ->expects($this->once())
        ->method('isUploadable')
        ->with('Vich\UploaderBundle\Tests\DummyEntity')
        ->will($this->returnValue(false));
    
        $this->handler
        ->expects($this->never())
        ->method('clean');
    
        $this->adapter
        ->expects($this->never())
        ->method('recomputeChangeSet');
    
        $this->listener->onUpload($this->event);
    }
}
