<?php

namespace Vich\UploaderBundle\Tests\Injector;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Injector\FileInjector;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\GaufretteStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * FileInjectorTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjectorTest extends TestCase
{
    /**
     * @var Vich\UploaderBundle\Storage\GaufretteStorage
     */
    protected $storage;

    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        $this->storage = $this->getMockStorage();
    }

    /**
     * Test inject one file.
     */
    public function testInjectsOneFile(): void
    {
        $obj = $this->createMock(DummyEntity::class);

        $fileMapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();
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
    public function testPropertyIsNullWhenFileNamePropertyIsNull(): void
    {
        $obj = $this->createMock(DummyEntity::class);

        $fileMapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->willReturn(null);

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
    }

    /**
     * Gets a mock storage.
     *
     * @return Vich\UploaderBundle\Storage\GaufretteStorage Storage
     */
    protected function getMockStorage()
    {
        return $this->getMockBuilder(GaufretteStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
