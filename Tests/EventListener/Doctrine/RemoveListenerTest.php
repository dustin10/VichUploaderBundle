<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\Persistence\Proxy;
use Vich\UploaderBundle\EventListener\Doctrine\RemoveListener;

/**
 * Doctrine RemoveListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class RemoveListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp()
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

        $this->assertSame(['preRemove', 'postRemove'], $events);
    }

    public function testPreRemove()
    {
        $this->object = $this->getEntityProxyMock();
        $this->object
            ->expects($this->once())
            ->method('__load');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxy')
            ->will($this->returnValue(true));

        $this->listener->preRemove($this->event);
    }

    public function testPreRemoveSkipNonUploadable()
    {
        $this->object = $this->getEntityProxyMock();
        $this->object
            ->expects($this->never())
            ->method('__load');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxy')
            ->will($this->returnValue(false));

        $this->listener->preRemove($this->event);
    }

    /**
     * Test the postRemove method.
     */
    public function testPostRemove()
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
            ->will($this->returnValue([
                ['propertyName' => 'field_name'],
            ]));

        $this->handler
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, 'field_name');

        $this->listener->postRemove($this->event);
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    public function testPostRemoveSkipsNonUploadable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('Vich\UploaderBundle\Tests\DummyEntity')
            ->will($this->returnValue(false));

        $this->handler
            ->expects($this->never())
            ->method('remove');

        $this->listener->postRemove($this->event);
    }

    /**
     * Creates a mock doctrine entity proxy.
     *
     * @return Proxy
     */
    protected function getEntityProxyMock()
    {
        return $this->getMockBuilder('Doctrine\Common\Persistence\Proxy')
            ->setMockClassName('VichUploaderEntityProxy')
            ->getMock();
    }
}
