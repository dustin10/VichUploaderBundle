<?php

namespace Vich\UploaderBundle\Tests\Storage;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * Common tests for all storage implementations.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class StorageTestCase extends TestCase
{
    protected PropertyMappingFactory|\PHPUnit\Framework\MockObject\MockObject$factory;

    protected PropertyMapping|\PHPUnit\Framework\MockObject\MockObject $mapping;

    /**
     * @var DummyEntity
     */
    protected $object;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * Returns the storage implementation to test.
     */
    abstract protected function getStorage(): StorageInterface;

    /**
     * Sets up the test.
     */
    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new DummyEntity();
        $this->storage = $this->getStorage();

        $this->factory
            ->method('fromObject')
            ->with($this->object)
            ->willReturn([$this->mapping]);

        // and initialize the virtual filesystem
        $this->root = vfsStream::setup('vich_uploader_bundle', null, [
            'uploads' => [
                'test.txt' => 'some content',
            ],
            'storage' => [
                'vich_uploader_bundle' => [],
            ],
        ]);
    }

    public static function emptyFilenameProvider(): array
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolvePathWithEmptyFile(?string $filename): void
    {
        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn($filename);

        $this->factory
            ->expects(self::once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        self::assertNull($this->storage->resolvePath($this->object, 'file_field'));
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolveUriWithEmptyFile(?string $filename): void
    {
        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn($filename);

        $this->factory
            ->expects(self::once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        self::assertNull($this->storage->resolvePath($this->object, 'file_field'));
    }

    protected function getValidUploadDir(): string
    {
        return $this->root->url().\DIRECTORY_SEPARATOR.'uploads';
    }
}
