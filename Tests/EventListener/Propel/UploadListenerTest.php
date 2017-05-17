<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use Vich\UploaderBundle\EventListener\Propel\UploadListener;

/**
 * Propel remove listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp()
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

        $this->assertArrayHasKey('propel.pre_update', $events);
        $this->assertArrayHasKey('propel.pre_insert', $events);
    }

    public function testOnUpload()
    {
        $this->handler
            ->expects($this->once())
            ->method('upload')
            ->with($this->object, self::FIELD_NAME);

        $this->listener->onUpload($this->event);
    }
}
