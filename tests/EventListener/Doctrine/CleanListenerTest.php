<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Persistence\Event\ManagerEventArgs;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\EventListener\Doctrine\CleanListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine CleanListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @extends ListenerTestCase<CleanListener>
 */
#[AllowMockObjectsWithoutExpectations]
class CleanListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new CleanListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }

    /**
     * Test the preUpdate method.
     */
    #[Test]
    public function preUpdate(): void
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
            ->method('clean')
            ->with($this->object, 'field_name');

        $this->adapter
            ->expects($this->once())
            ->method('recomputeChangeSet')
            ->with($this->event);

        $this->listener->preUpdate($this->event);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    #[Test]
    public function preUpdateSkipsNonUploadable(): void
    {
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->handler
            ->expects($this->never())
            ->method('clean');

        $this->adapter
            ->expects($this->never())
            ->method('recomputeChangeSet');

        $this->listener->preUpdate($this->event);
    }

    #[Test]
    public function preFlush(): void
    {
        $event = $this->createStub(ManagerEventArgs::class);
        $this->adapter
            ->expects($this->once())
            ->method('getObjectsToFlush')
            ->with($event, false)
            ->willReturn([$this->object]);
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
            ->method('clean')
            ->with($this->object, 'field_name');

        $this->listener->preFlush($event);
    }

    #[Test]
    public function preFlushSkipsNonUploadable(): void
    {
        $event = $this->createStub(ManagerEventArgs::class);
        $this->adapter
            ->method('getObjectsToFlush')
            ->willReturn([$this->object]);
        $this->metadata
            ->expects($this->once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->handler
            ->expects($this->never())
            ->method('clean');

        $this->listener->preFlush($event);
    }
}
