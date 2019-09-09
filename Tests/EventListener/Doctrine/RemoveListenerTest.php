<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\Persistence\Proxy;
use Vich\UploaderBundle\EventListener\Doctrine\RemoveListener;
use Vich\UploaderBundle\Tests\DummyEntity;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new RemoveListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents(): void
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertSame(['preRemove', 'postRemove'], $events);
    }

    public function testPreRemove(): void
    {
        $this->object = $this->getEntityProxyMock();
        $this->object
            ->expects($this->once())
            ->method('__load');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxy')
            ->willReturn(true);

        $this->listener->preRemove($this->event);
    }

    public function testPreRemoveSkipNonUploadable(): void
    {
        $this->object = $this->getEntityProxyMock();
        $this->object
            ->expects($this->never())
            ->method('__load');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxy')
            ->willReturn(false);

        $this->listener->preRemove($this->event);
    }

    /**
     * Test the postRemove method.
     */
    public function testPostRemove(): void
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class, self::MAPPING_NAME)
            ->willReturn([
                ['propertyName' => 'field_name'],
            ]);

        $this->handler
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, 'field_name');

        $this->listener->postRemove($this->event);
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    public function testPostRemoveSkipsNonUploadable(): void
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

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
        return $this->getMockBuilder(Proxy::class)
            ->setMockClassName('VichUploaderEntityProxy')
            ->getMock();
    }
}
