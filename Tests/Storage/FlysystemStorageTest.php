<?php

namespace Vich\UploaderBundle\Tests\Storage;

use org\bovigo\vfs\vfsStream;

use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMapping
     */
    protected $mapping;

    /**
     * @var \Oneup\FlysystemBundle\Filesystem\FilesystemMap $filesystemMap
     */
    protected $filesystemMap;

    /**
     * @var FlysystemStorage
     */
    protected $storage;

    /**
     * @var \Vich\UploaderBundle\Tests\DummyEntity
     */
    protected $object;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    protected function setUp()
    {
        $this->mapping = $this->getMappingMock();
        $this->object = new DummyEntity();
        $this->factory = $this->getFactoryMock();
        $this->filesystemMap = $this->getFilesystemMapMock();

        $this->factory
            ->expects($this->any())
            ->method('fromObject')
            ->with($this->object)
            ->will($this->returnValue(array($this->mapping)));

        $this->storage = new FlysystemStorage($this->factory, $this->filesystemMap);

        // and initialize the virtual filesystem
        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'uploads' => array(
                'test.txt' => 'some content'
            ),
        ));
    }

    public function testUpload()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getFilesystemMock();

        $file
            ->expects($this->once())
            ->method('getRealPath')
            ->will($this->returnValue($this->root->url() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'test.txt'));
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('originalName.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with(
                'originalName.txt',
                $this->isType('resource'),
                $this->isType('array')
            );

        $this->storage->upload($this->object);
    }

    public function testRemove()
    {
        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('test.txt');

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('dir')
            ->will($this->returnValue($filesystem));

        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('dir'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('test.txt'));

        $this->storage->remove($this->object);
    }

    public function testRemoveOnNonExistentFile()
    {
        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('not_found.txt')
            ->will($this->throwException(new \League\Flysystem\FileNotFoundException('dummy path')));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('dir')
            ->will($this->returnValue($filesystem));

        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('dir'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('not_found.txt'));

        $this->storage->remove($this->object);
    }

    /**
     * Creates a mock factory.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getFactoryMock()
    {
        return $this
            ->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a filesystem map mock.
     *
     * @return \Oneup\FlysystemBundle\Filesystem\FilesystemMap The filesystem map.
     */
    protected function getFilesystemMapMock()
    {
        return $this
            ->getMockBuilder('Oneup\FlysystemBundle\Filesystem\FilesystemMap')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a filesystem mock.
     *
     * @return \League\Flysystem\FilesystemInterface The filesystem object.
     */
    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('League\Flysystem\FilesystemInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mapping mock.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping The property mapping.
     */
    protected function getMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
