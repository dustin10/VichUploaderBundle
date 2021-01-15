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
final class FileInjectorTest extends TestCase
{
    /**
     * @var GaufretteStorage|\PHPUnit\Framework\MockObject\MockObject
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

        $fileMapping = $this->getMockBuilder(PropertyMapping::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage
            ->expects(self::once())
            ->method('resolvePath')
            ->willReturn(null);

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
    }

    /**
     * Gets a mock storage.
     *
     * @return GaufretteStorage
     */
    protected function getMockStorage()
    {
        return $this->getMockBuilder(GaufretteStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
