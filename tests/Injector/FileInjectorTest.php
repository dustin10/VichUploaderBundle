<?php

namespace Vich\UploaderBundle\Tests\Injector;

use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Injector\FileInjector;
use Vich\UploaderBundle\Storage\GaufretteStorage;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * FileInjectorTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class FileInjectorTest extends TestCase
{
    protected GaufretteStorage|MockObject $storage;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(GaufretteStorage::class);
    }

    /**
     * Test inject one file.
     */
    public function testInjectsOneFile(): void
    {
        $obj = $this->createMock(DummyEntity::class);

        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping
            ->expects(self::once())
            ->method('getFilePropertyName')
            ->willReturn('file_field');
        $fileMapping
            ->expects(self::once())
            ->method('setFile');

        $this->storage
            ->expects(self::once())
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
    public function testPropertyIsNullWhenFileNamePropertyIsNull(): void
    {
        $obj = $this->createMock(DummyEntity::class);

        $fileMapping = $this->getPropertyMappingMock();

        $this->storage
            ->expects(self::once())
            ->method('resolvePath')
            ->willReturn(null);

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
    }
}
