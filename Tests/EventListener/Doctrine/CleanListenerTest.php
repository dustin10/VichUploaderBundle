<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Vich\UploaderBundle\EventListener\Doctrine\CleanListener;

/**
 * Doctrine CleanListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
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

        $this->assertSame(array('preUpdate'), $events);
    }

    /**
     * Test the preUpdate method.
     */
    public function testPreUpdate()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(true));

        $this->handler
            ->expects($this->once())
            ->method('clean')
            ->with($this->object, self::MAPPING_NAME);

        $this->adapter
            ->expects($this->once())
            ->method('recomputeChangeSet')
            ->with($this->event);

        $this->listener->preUpdate($this->event);
    }


    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPreUpdateSkipsNonUploadable()
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

        $this->listener->preUpdate($this->event);
    }
}
