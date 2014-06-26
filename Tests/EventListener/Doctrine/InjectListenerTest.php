<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Vich\UploaderBundle\EventListener\Doctrine\InjectListener;

/**
 * Doctrine InjectListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
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

        $this->assertSame(array('postLoad'), $events);
    }

    /**
     * Test the postLoad method.
     */
    public function testPostLoad()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('inject')
            ->with($this->object, self::MAPPING_NAME);

        $this->listener->postLoad($this->event);
    }

    /**
     * Test that postLoad skips non uploadable entity.
     */
    public function testPostLoadSkipsNonUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('inject', self::MAPPING_NAME);

        $this->listener->postLoad($this->event);
    }
}
