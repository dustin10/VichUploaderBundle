<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Gaufrette\Exception\FileNotFound;
use org\bovigo\vfs\vfsStream;

use Vich\UploaderBundle\Storage\GaufretteStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * GaufretteStorageTest.
 *
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class GaufretteStorageTest extends \PHPUnit_Framework_TestCase
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
     * @var \Vich\UploaderBundle\Tests\DummyEntity
     */
    protected $object;

    /**
     * @var FileSystemStorage
     */
    protected $storage;

    /**
     * @var \Knp\Bundle\GaufretteBundle\FilesystemMap $factory
     */
    protected $filesystemMap;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
        $this->mapping = $this->getMappingMock();
        $this->object = new DummyEntity();
        $this->filesystemMap = $this->getFilesystemMapMock();
        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap);

        $this->factory
            ->expects($this->any())
            ->method('fromObject')
            ->with($this->object)
            ->will($this->returnValue(array($this->mapping)));

        // and initialize the virtual filesystem
        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'uploads' => array(
                'test.txt' => 'some content'
            ),
        ));
    }

    /**
     * Tests the upload method skips a mapping which has a non
     * uploadable property value.
     *
     * @dataProvider    invalidFileProvider
     * @group           upload
     */
    public function testUploadSkipsMappingOnInvalid($file)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->never())
            ->method('hasNamer');

        $this->mapping
            ->expects($this->never())
            ->method('getNamer');

        $this->mapping
            ->expects($this->never())
            ->method('getFileName');

        $this->storage->upload($this->object);
    }

    public function invalidFileProvider()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            // skipped because null
            array( null ),
            // skipped because not even a file
            array( new \DateTime() ),
            // skipped because not instance of UploadedFile
            array( $file ),
        );
    }

    /**
     * Test the remove method does not remove a file that is configured
     * to not be deleted upon removal of the entity.
     */
    public function testRemoveSkipsConfiguredNotToDeleteOnRemove()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(false));

        $this->mapping
            ->expects($this->never())
            ->method('getFileName');

        $this->storage->remove($this->object);
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     */
    public function testRemoveSkipsNullFileNameProperty()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue(null));

        $this->mapping
            ->expects($this->never())
            ->method('getUploadDir');

        $this->storage->remove($this->object);
    }

    /**
     * Test the resolve path method.
     *
     * @dataProvider protocolProvider
     */
    public function testResolvePath($protocol)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file')
            ->will($this->returnValue($this->mapping));

        $this->storage = new GaufretteStorage($this->factory, $this->filesystemMap, $protocol);
        $path = $this->storage->resolvePath($this->object, 'file');

        $this->assertEquals($protocol.'://filesystemKey/file.txt', $path);
    }

    public function protocolProvider()
    {
        return array(
            array( 'gaufrette' ),
            array( 'data' ),
        );
    }

    /**
     * Test the remove method does delete file from gaufrette filesystem
     */
    public function testThatRemoveMethodDoesDeleteFile()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));
        $this->mapping
            ->expects($this->any())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('file.txt');

        $this
            ->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $this->storage->remove($this->object, 'file');
    }

    /**
     * Test that FileNotFound exception is catched
     */
    public function testRemoveNotFoundFile()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));
        $this->mapping
            ->expects($this->any())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('file.txt')
            ->will($this->throwException(new FileNotFound('File Not Found')));

        $this
            ->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $this->storage->remove($this->object, 'file');
    }

    /**
     * Test the resolve path method throws exception
     * when an invaid field name is specified.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testResolvePathThrowsExceptionOnInvalidFieldName()
    {
        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'oops')
            ->will($this->returnValue(null));

        $this->storage->resolvePath($this->object, 'oops');
    }

    public function testUploadSetsMetadataWhenUsingMetadataSupporterAdapter()
    {
        $filesystem = $this->getFilesystemMock();
        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\MetadataSupporter')
            ->disableOriginalConstructor()
            ->setMethods(array('setMetadata', 'getMetadata'))
            ->getMock();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getValidUploadDir() . DIRECTORY_SEPARATOR . 'test.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));

        $imb = $this
            ->getMockBuilder('\Gaufrette\Stream\InMemoryBuffer')
            ->disableOriginalConstructor()
            ->setMethods(array('open', 'write', 'close'))
            ->getMock();

        $imb
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('write')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));

        $filesystem
            ->expects($this->once())
            ->method('createStream')
            ->will($this->returnValue($imb));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $adapter
            ->expects($this->once())
            ->method('setMetadata')
            ->will($this->returnValue(null));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));

        $this->storage->upload($this->object);
    }

    public function testUploadDoesNotSetMetadataWhenUsingNonMetadataSupporterAdapter()
    {
        $adapter = $this->getMockBuilder('\Gaufrette\Adapter\Apc')
            ->disableOriginalConstructor()
            ->getMock();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename'));

        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getValidUploadDir() . DIRECTORY_SEPARATOR . 'test.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('filesystemKey'));

        $filesystem = $this->getFilesystemMock();

        $imb = $this
            ->getMockBuilder('\Gaufrette\Stream\InMemoryBuffer')
            ->disableOriginalConstructor()
            ->setMethods(array('open', 'write', 'close'))
            ->getMock();

        $imb
            ->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('write')
            ->will($this->returnValue(true));

        $imb
            ->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));

        $filesystem
            ->expects($this->once())
            ->method('createStream')
            ->will($this->returnValue($imb));

        $this->filesystemMap
            ->expects($this->once())
            ->method('get')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $adapter
            ->expects($this->never())
            ->method('setMetadata')
            ->will($this->returnValue(null));

        $filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($adapter));

        $this->storage->upload($this->object);
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
     * Creates a mock of gaufrette filesystem map.
     *
     * @return \Knp\Bundle\GaufretteBundle\FilesystemMap The filesystem map.
     */
    protected function getFilesystemMapMock()
    {
        return $this
            ->getMockBuilder('Knp\Bundle\GaufretteBundle\FilesystemMap')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock of gaufrette filesystem.
     *
     * @return \Gaufrette\Filesystem The gaufrette filesystem object.
     */
    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('\Gaufrette\Filesystem')
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

    protected function getValidUploadDir()
    {
        return $this->root->url() . DIRECTORY_SEPARATOR . 'uploads';
    }
}
