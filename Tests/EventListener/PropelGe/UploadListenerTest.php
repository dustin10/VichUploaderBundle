<?php

namespace Vich\UploaderBundle\Tests\EventListener\PropelGe;

use Vich\UploaderBundle\EventListener\PropelGe\UploadListener;

/**
 * Propel remove listener test case.
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class UploadListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test
     */
    public function setUp()
    {
        parent::setUp();
    
        $this->listener = new UploadListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }
    
    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('model.save.pre', $events);
    }
    
    /**
     * Test the onUpload method.
     */
    public function testOnUpload()
    {
        $this->adapter
        ->expects($this->never())
        ->method('recomputeChangeSet')
        ->with($this->event);
    
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
        ->method('upload')
        ->with($this->object, self::FIELD_NAME);
    
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
    
        $this->adapter
        ->expects($this->never())
        ->method('recomputeChangeSet');
    
        $this->handler
        ->expects($this->never())
        ->method('upload');
    
        $this->listener->onUpload($this->event);
    }
}
