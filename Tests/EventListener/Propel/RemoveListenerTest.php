<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use Vich\UploaderBundle\EventListener\Propel\RemoveListener;

/**
 * Propel remove listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->listener = new RemoveListener(self::MAPPING_NAME, $this->adapter, $this->handler);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('propel.post_delete', $events);
    }

    public function testOnDelete()
    {
        $this->handler
            ->expects($this->once())
            ->method('delete')
            ->with($this->object, self::MAPPING_NAME);

        $this->listener->onDelete($this->event);
    }
}
