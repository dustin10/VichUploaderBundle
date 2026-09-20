<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\Proxy\Proxy;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
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
#[AllowMockObjectsWithoutExpectations]
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

    #[Test]
    public function preRemove(): void
    {
        $this->object = $this->getEntityProxyMock('One');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxyOne')
            ->willReturn(true);

        $this->object
            ->expects($this->once())
            ->method('__load');

        $this->event = $this->getEventStub();
        $this->event->method('getObject')->willReturn($this->object);

        $this->listener->preRemove($this->event);
    }

    #[Test]
    public function preRemoveSkipNonUploadable(): void
    {
        $this->object = $this->getEntityProxyMock('Two');
        $this->object
            ->expects($this->never())
            ->method('__load');

        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with('VichUploaderEntityProxyTwo')
            ->willReturn(false);

        $this->event = $this->getEventStub();
        $this->event->method('getObject')->willReturn($this->object);

        $this->listener->preRemove($this->event);
    }

    #[Test]
    public function postFlush(): void
    {
        // isUploadable
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(true);

        $this->listener->preRemove($this->event);

        $this->metadata
            ->expects($this->once())
            ->method('getUploadableFields')
            ->with(DummyEntity::class)
            ->willReturn([['propertyName' => 'field_name']])
        ;

        $this->handler
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, 'field_name')
        ;

        $this->listener->postFlush();
    }

    /**
     * Test that postRemove skips non uploadable entity.
     */
    #[Test]
    public function postFlushSkipsNonUploadable(): void
    {
        // isUploadable
        $this->metadata
            ->expects($this->once())
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

    private function getEntityProxyMock(string $postfix): Proxy|MockObject
    {
        return $this->getMockBuilder(Proxy::class)
            ->setMockClassName('VichUploaderEntityProxy'.$postfix)
            ->getMock();
    }
}
