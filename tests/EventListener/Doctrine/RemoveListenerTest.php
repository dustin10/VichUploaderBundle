<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\Proxy\Proxy;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\EventListener\Doctrine\RemoveListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine RemoveListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @extends ListenerTestCase<RemoveListener>
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

    public function testPreRemove(): void
    {
        $this->object = $this->getEntityProxyMock();

        $this->metadata
            ->expects(self::once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxy')
            ->willReturn(true);

        $this->object
            ->expects(self::once())
            ->method('__load');

        $this->event = $this->getEventMock();
        $this->event->method('getObject')->willReturn($this->object);

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

        $this->event = $this->getEventMock();
        $this->event->method('getObject')->willReturn($this->object);

        $this->listener->preRemove($this->event);
    }

    public function testPostFlush(): void
    {
        // isUploadable
        $this->metadata
            ->expects(self::once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->listener->preRemove($this->event);

        $this->metadata
            ->expects(self::once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([['propertyName' => 'field_name']])
        ;

        $this->handler
            ->expects(self::once())
            ->method('remove')
            ->with($this->object, 'field_name')
        ;

        $this->listener->postFlush();
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    public function testPostFlushSkipsNonUploadable(): void
    {
        // isUploadable
        $this->metadata
            ->expects(self::once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->listener->preRemove($this->event);

        $this->metadata
            ->expects(self::never())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([['propertyName' => 'field_name']])
        ;

        $this->handler
            ->expects(self::never())
            ->method('remove')
            ->with($this->object, 'field_name')
        ;

        $this->listener->postFlush();
    }

    protected function getEntityProxyMock(): Proxy|MockObject
    {
        return $this->getMockBuilder(Proxy::class)
            ->setMockClassName('VichUploaderEntityProxy')
            ->getMock();
    }
}
