<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Vich\UploaderBundle\EventListener\Doctrine\UploadListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine UploadListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new UploadListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents(): void
    {
        $events = $this->listener->getSubscribedEvents();

        $this->assertSame(['prePersist', 'preUpdate'], $events);
    }

    /**
     * Tests the prePersist method.
     */
    public function testPrePersist(): void
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
            ->method('upload')
            ->with($this->object, 'field_name');

        $this->listener->prePersist($this->event);
    }

    /**
     * Tests that prePersist skips non-uploadable entity.
     */
    public function testPrePersistSkipsNonUploadable(): void
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->handler
            ->expects($this->never())
            ->method('upload');

        $this->listener->prePersist($this->event);
    }

    /**
     * Test the preUpdate method.
     */
    public function testPreUpdate(): void
    {
        $this->adapter
            ->expects($this->once())
            ->method('recomputeChangeSet')
            ->with($this->event);

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
            ->method('upload')
            ->with($this->object, 'field_name');

        $this->listener->preUpdate($this->event);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPreUpdateSkipsNonUploadable(): void
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->adapter
            ->expects($this->never())
            ->method('recomputeChangeSet');

        $this->handler
            ->expects($this->never())
            ->method('upload');

        $this->listener->preUpdate($this->event);
    }
}
