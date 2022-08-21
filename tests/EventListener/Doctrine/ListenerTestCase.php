<?php

namespace Vich\UploaderBundle\Tests\EventListener\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\EventListener\Doctrine\CleanListener;
use Vich\UploaderBundle\EventListener\Doctrine\InjectListener;
use Vich\UploaderBundle\EventListener\Doctrine\RemoveListener;
use Vich\UploaderBundle\EventListener\Doctrine\UploadListener;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * Doctrine listener test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class ListenerTestCase extends TestCase
{
    public const MAPPING_NAME = 'dummy_mapping';

    public static bool $usePreUpdateEventArgs = false;

    protected AdapterInterface|MockObject $adapter;

    protected MetadataReader|MockObject $metadata;

    protected UploadHandler|MockObject $handler;

    protected EventArgs|PreUpdateEventArgs|MockObject $event;

    public DummyEntity|MockObject $object;

    protected CleanListener|InjectListener|RemoveListener|UploadListener|null $listener;

    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->metadata = $this->createMock(MetadataReader::class);
        $this->handler = $this->createMock(UploadHandler::class);
        $this->object = new DummyEntity();
        $this->event = $this->createMock(EventArgs::class);

        $that = $this;

        // the adapter is always used to return the object
        $this->adapter
            ->method('getObjectFromArgs')
            ->with($this->event)
            ->willReturnCallback(fn () => $that->object);
    }
}
