<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Persistence\Proxy;
use Vich\UploaderBundle\EventListener\Doctrine\RemoveListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine RemoveListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class RemoveListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new RemoveListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = $this->listener->getSubscribedEvents();

        self::assertSame(['preRemove', 'postRemove'], $events);
    }

    public function testPreRemove(): void
    {
        $this->object = $this->getEntityProxyMock();
        $this->object
            ->expects(self::once())
            ->method('__load');

        $this->metadata
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxy')
            ->willReturn(false);

        $this->listener->preRemove($this->event);
    }

    public function testPostRemove(): void
    {
        $this->metadata
            ->expects(self::once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->metadata
            ->expects(self::once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class, self::MAPPING_NAME)
            ->willReturn([
                ['propertyName' => 'field_name'],
            ]);

        $this->handler
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->handler
            ->expects($this->never())
            ->method('remove');

        $this->listener->postRemove($this->event);
    }

    /**
     * @return Proxy&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEntityProxyMock(): Proxy
    {
        return $this->getMockBuilder(Proxy::class)
            ->setMockClassName('VichUploaderEntityProxy')
            ->getMock();
    }
}
