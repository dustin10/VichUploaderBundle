<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Vich\UploaderBundle\EventListener\Doctrine\CleanListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine CleanListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @extends ListenerTestCase<CleanListener>
 */
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
    public function testPreUpdate(): void
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
            ->method('clean')
            ->with($this->object, 'field_name');

        $this->adapter
            ->expects(self::once())
            ->method('recomputeChangeSet')
            ->with($this->event);

        $this->listener->preUpdate($this->event);
    }

    /**
     * Test that preUpdate skips non uploadable entity.
     */
    public function testPreUpdateSkipsNonUploadable(): void
    {
        $this->metadata
            ->expects(self::once())
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
}
