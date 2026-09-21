<?php

namespace Vich\UploaderBundle\Tests\Injector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Injector\FileInjector;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class FileInjectorTest extends TestCase
{
    protected StorageInterface|MockObject $storage;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
    }

    /**
     * Test inject one file.
     */
    #[Test]
    public function injectsOneFile(): void
    {
        $obj = $this->createStub(DummyEntity::class);

        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping
            ->expects($this->once())
            ->method('getFilePropertyName')
            ->willReturn('file_field');
        $fileMapping
            ->expects($this->once())
            ->method('setFile');

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($obj, 'file_field')
            ->willReturn('/uploadDir/file.txt');

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
    }

    /**
     * Test that if the file name property returns a null value
     * then no file is injected.
     */
    #[Test]
    public function propertyIsNullWhenFileNamePropertyIsNull(): void
    {
        $obj = $this->createStub(DummyEntity::class);

        $fileMapping = $this->getPropertyMappingStub();

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->willReturn(null);

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
    }
}
