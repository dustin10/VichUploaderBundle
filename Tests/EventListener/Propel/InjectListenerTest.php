<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use Vich\UploaderBundle\EventListener\Propel\InjectListener;

/**
 * Propel remove listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class InjectListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp()
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

        $this->assertArrayHasKey('propel.post_hydrate', $events);
    }

    public function testOnHydrate()
    {
        $this->handler
            ->expects($this->once())
            ->method('inject')
            ->with($this->object, self::FIELD_NAME);

        $this->listener->onHydrate($this->event);
    }
}
