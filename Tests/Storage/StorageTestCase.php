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
    /**
     * @var PropertyMappingFactory
     */
    protected $factory;

    /**
     * @var PropertyMapping
     */
    protected $mapping;

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
     *
     * @return StorageInterface
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
            ->expects($this->any())
            ->method('fromObject')
            ->with($this->object)
            ->willReturn([$this->mapping]);

        // and initialize the virtual filesystem
        $this->root = vfsStream::setup('vich_uploader_bundle', null, [
            'uploads' => [
                'test.txt' => 'some content',
            ],
        ]);
    }

    public function emptyFilenameProvider(): array
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolvePathWithEmptyFile($filename): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($filename);

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->assertNull($this->storage->resolvePath($this->object, 'file_field'));
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolveUriWithEmptyFile($filename): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($filename);

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->assertNull($this->storage->resolvePath($this->object, 'file_field'));
    }

    protected function getValidUploadDir(): string
    {
        return $this->root->url().\DIRECTORY_SEPARATOR.'uploads';
    }
}
