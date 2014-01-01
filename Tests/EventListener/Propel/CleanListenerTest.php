<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use Vich\UploaderBundle\EventListener\Propel\CleanListener;

/**
 * Propel clean listener test case.
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

        $this->listener = new CleanListener(self::MAPPING_NAME, $this->adapter, $this->handler);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertArrayHasKey('propel.pre_update', $events);
    }

    public function testOnUpload()
    {
        $this->handler
            ->expects($this->once())
            ->method('clean')
            ->with($this->object, self::MAPPING_NAME);

        $this->listener->onUpload($this->event);
    }
}
