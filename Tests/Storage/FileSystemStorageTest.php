<?php

namespace Vich\UploaderBundle\Tests\Storage;

use org\bovigo\vfs\vfsStream;

use Vich\UploaderBundle\Storage\FileSystemStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * FileSystemStorageTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileSystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
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
     * @var FileSystemStorage
     */
    protected $storage;

    /**
     * @var vfsStreamDirectory
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
        $this->storage = new FileSystemStorage($this->factory);

        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'uploads' => array(
                'test.txt' => 'some content'
            ),
        ));

        $this->factory
            ->expects($this->any())
            ->method('fromObject')
            ->with($this->object)
            ->will($this->returnValue(array($this->mapping)));
    }

    /**
     * @dataProvider    invalidFileProvider
     * @group           upload
     */
    public function testUploadSkipsMappingOnInvalidFile()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(null));
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
     * Test that file upload moves uploaded file to correct directory and with
     * correct filename.
     *
     * @group upload
     */
    public function testUploadedFileIsCorrectlyMoved()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('/dir'));

        $file
            ->expects($this->once())
            ->method('move')
            ->with('/dir', 'filename.txt');

        $this->storage->upload($this->object);

    }

    /**
     * Test file upload when filename contains directories
     *
     * @dataProvider    filenameWithDirectoriesDataProvider
     * @group           upload
     */
    public function testFilenameWithDirectoriesIsUploadedToCorrectDirectory($dir, $filename, $expectedDir, $expectedFileName)
    {
        $name = 'lala';

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $namer = $this->getMockBuilder('Vich\UploaderBundle\Naming\NamerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $namer
            ->expects($this->once())
            ->method('name')
            ->with($this->mapping, $this->object)
            ->will($this->returnValue($filename));

        $this->mapping
            ->expects($this->once())
            ->method('getNamer')
            ->will($this->returnValue($namer));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnUpdate')
            ->will($this->returnValue(false));

        $this->mapping
            ->expects($this->once())
            ->method('hasNamer')
            ->will($this->returnValue(true));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue($dir));

        $file
            ->expects($this->once())
            ->method('move')
            ->with($expectedDir, $expectedFileName);

        $this->storage->upload($this->object);

    }

    public function filenameWithDirectoriesDataProvider()
    {
        return array(
            array(
                '/root_dir',
                '/dir_1/dir_2/filename.txt',
                '/root_dir/dir_1/dir_2',
                'filename.txt'
            ),
            array(
                '/root_dir',
                'dir_1/dir_2/filename.txt',
                '/root_dir/dir_1/dir_2',
                'filename.txt'
            ),
        );
    }

    /**
     * @dataProvider    removeDataProvider
     * @group           remove
     */
    public function testRemove($deleteOnRemove, $uploadDir, $fileName)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue($deleteOnRemove));

        // if the file should be deleted, we'll need its name
        if ($deleteOnRemove) {
            $this->mapping
                ->expects($this->once())
                ->method('getFileName')
                ->will($this->returnValue($fileName));
        } else {
            $this->mapping
                ->expects($this->never())
                ->method('getFileName');
        }

        // if the file should be deleted and we have its name, then we need the
        // upload dir
        if ($deleteOnRemove && $fileName !== null) {
            $this->mapping
                ->expects($this->once())
                ->method('getUploadDestination')
                ->will($this->returnValue($this->root->url() . DIRECTORY_SEPARATOR . $uploadDir));
        } else {
            $this->mapping
                ->expects($this->never())
                ->method('getUploadDestination');
        }

        $this->storage->remove($this->object);

        // the file should have been deleted
        if ($deleteOnRemove && $fileName !== null) {
            $this->assertFalse($this->root->hasChild($uploadDir . DIRECTORY_SEPARATOR . $fileName));
        }
    }

    public function removeDataProvider()
    {
        return array(
            //    deleteOnRemove    uploadDir   fileName
            // don't configured to be deleted upon removal of the entity
            array(false,            null,       null),
            // configured, but not file present
            array(true,             null,       null),
            // configured and present in the filesystem
            array(true,             '/uploads', 'test.txt'),
            // configured, but file already deleted
            array(true,             '/uploads', 'file.txt'),
        );
    }

    /**
     * Test the resolve path method.
     *
     * @group resolvePath
     */
    public function testResolvePath()
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('/tmp'));
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file')
            ->will($this->returnValue($this->mapping));

        $path = $this->storage->resolvePath($this->object, 'file');

        $this->assertEquals(sprintf('/tmp%sfile.txt', DIRECTORY_SEPARATOR), $path);
    }

    /**
     * Test the resolve path method throws exception
     * when an invaid field name is specified.
     *
     * @expectedException   InvalidArgumentException
     * @group               resolvePath
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

    /**
     * Test the resolve uri
     *
     * @dataProvider    resolveUriDataProvider
     * @group           resolveUri
     */
    public function testResolveUri($uploadDir, $file, $uri)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue($uploadDir));

        $this->mapping
            ->expects($this->once())
            ->method('getUriPrefix')
            ->will($this->returnValue('/uploads'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($file));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file')
            ->will($this->returnValue($this->mapping));

        $this->assertEquals($uri, $this->storage->resolveUri($this->object, 'file'));
    }

    public function resolveUriDataProvider()
    {
        return array(
            array(
                '/abs/path/web/uploads',
                'file.txt',
                '/uploads/file.txt'
            ),
            array(
                'c:\abs\path\web\uploads',
                'file.txt',
                '/uploads/file.txt'
            ),
            array(
                '/abs/path/web/project/web/uploads',
                'file.txt',
                '/uploads/file.txt'
            ),
            array(
                '/abs/path/web/project/web/uploads/foo',
                'custom/dir/file.txt',
                '/uploads/foo/custom/dir/file.txt'
            ),
            array(
                '/abs/path/web/project/web/uploads/custom/dir',
                'file.txt',
                '/uploads/custom/dir/file.txt'
            ),
        );
    }

    /**
     * Creates a mock mapping-factory.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getFactoryMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
