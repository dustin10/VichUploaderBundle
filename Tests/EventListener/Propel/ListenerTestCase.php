<?php

namespace Vich\UploaderBundle\Tests\EventListener\Propel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Propel listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class ListenerTestCase extends TestCase
{
    public const FIELD_NAME = 'file';

    public const MAPPING_NAME = 'mapping_name';

    /**
     * @var AdapterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $adapter;

    /**
     * @var UploadHandler&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $handler;

    /**
     * @var MetadataReader&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadata;

    /**
     * @var PropelUploaderListener
     */
    protected $listener;

    /**
     * @var GenericEvent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    /**
     * @var DummyEntity
     */
    protected $object;

    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        $this->adapter = $this->getAdapterMock();
        $this->handler = $this->getHandlerMock();
        $this->object = new DummyEntity();
        $this->event = $this->getEventMock();
        $this->metadata = $this->getMetadataReaderMock();

        // the adapter is always used to return the object
        $this->adapter
            ->method('getObjectFromArgs')
            ->with($this->event)
            ->willReturn($this->object);

        $this->metadata
            ->method('getUploadableFields')
            ->with(DummyEntity::class, self::MAPPING_NAME)
            ->willReturn([
                ['propertyName' => self::FIELD_NAME],
            ]);
    }

    /**
     * @return AdapterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAdapterMock(): AdapterInterface
    {
        return $this->createMock(AdapterInterface::class);
    }

    /**
     * @return UploadHandler&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getHandlerMock(): UploadHandler
    {
        return $this->getMockBuilder(UploadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return GenericEvent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEventMock(): GenericEvent
    {
        return $this->getMockBuilder(GenericEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MetadataReader&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMetadataReaderMock(): MetadataReader
    {
        return $this->getMockBuilder(MetadataReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
