<?php

namespace Vich\UploaderBundle\Tests\Storage;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\HttpFoundation\File\File;
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
    abstract protected function getStorage();

    /**
     * Sets up the test.
     */
    protected function setUp()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new DummyEntity();
        $this->storage = $this->getStorage();

        $this->factory
            ->expects($this->any())
            ->method('fromObject')
            ->with($this->object)
            ->will($this->returnValue([$this->mapping]));

        // and initialize the virtual filesystem
        $this->root = vfsStream::setup('vich_uploader_bundle', null, [
            'uploads' => [
                'test.txt' => 'some content',
            ],
        ]);
    }

    public function invalidFileProvider()
    {
        $file = new File('dummy.file', false);

        return [
            // skipped because null
            [null],
            // skipped because not even a file
            [new \DateTime()],
            // skipped because not instance of UploadedFile
            [$file],
        ];
    }

    public function emptyFilenameProvider()
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolvePathWithEmptyFile($filename)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($filename));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $this->assertNull($this->storage->resolvePath($this->object, 'file_field'));
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolveUriWithEmptyFile($filename)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($filename));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $this->assertNull($this->storage->resolvePath($this->object, 'file_field'));
    }

    protected function getValidUploadDir()
    {
        return $this->root->url().DIRECTORY_SEPARATOR.'uploads';
    }
}
