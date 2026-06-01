<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Vich\UploaderBundle\EventListener\Doctrine\InjectListener;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine InjectListener test.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @extends ListenerTestCase<InjectListener>
 */
class InjectListenerTest extends ListenerTestCase
{
    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new InjectListener(self::MAPPING_NAME, $this->adapter, $this->metadata, $this->handler);
    }

    /**
     * Test the postLoad method.
     */
    public function testPostLoad(): void
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
            ->method('inject')
            ->with($this->object, 'field_name');

        $this->listener->postLoad($this->event);
    }

    /**
     * Test that postLoad skips non uploadable entity.
     */
    public function testPostLoadSkipsNonUploadable(): void
    {
        $this->metadata
            ->expects(self::once())
            ->method('isUploadable')
            ->with(DummyEntity::class)
            ->willReturn(false);

        $this->handler
            ->expects($this->never())
            ->method('inject')
            ->with(self::MAPPING_NAME);

        $this->listener->postLoad($this->event);
    }
}
