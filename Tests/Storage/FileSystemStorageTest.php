<?php

namespace Vich\UploaderBundle\Tests\Storage;

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
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
    }

    /**
     * Tests the upload method skips a mapping which has a null
     * uploadable property value.
     */
    public function testUploadSkipsMappingOnNullFile()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
                ->expects($this->once())
                ->method('getPropertyValue')
                ->will($this->returnValue(null));

        $mapping
                ->expects($this->never())
                ->method('hasNamer');

        $mapping
                ->expects($this->never())
                ->method('getNamer');

        $mapping
                ->expects($this->never())
                ->method('getFileNameProperty');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new FileSystemStorage($this->factory);
        $storage->upload($obj);
    }

    /**
     * Tests the upload method skips a mapping which has an uploadable
     * field property value that is not an instance of UploadedFile.
     */
    public function testUploadSkipsMappingOnNonUploadedFileInstance()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
                ->disableOriginalConstructor()
                ->getMock();

        $mapping
                ->expects($this->once())
                ->method('getPropertyValue')
                ->will($this->returnValue($file));

        $mapping
                ->expects($this->never())
                ->method('hasNamer');

        $mapping
                ->expects($this->never())
                ->method('getNamer');

        $mapping
                ->expects($this->never())
                ->method('getFileNameProperty');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new FileSystemStorage($this->factory);
        $storage->upload($obj);
    }

    /**
     * Test the remove method does not remove a file that is configured
     * to not be deleted upon removal of the entity.
     */
    public function testRemoveSkipsConfiguredNotToDeleteOnRemove()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
                ->expects($this->once())
                ->method('getDeleteOnRemove')
                ->will($this->returnValue(false));

        $mapping
                ->expects($this->never())
                ->method('getFileNameProperty');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new FileSystemStorage($this->factory);
        $storage->remove($obj);
    }

    /**
     * Test the remove method skips trying to remove a file whose file name
     * property value returns null.
     */
    public function testRemoveSkipsNullFileNameProperty()
    {
        $obj = new DummyEntity();

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue(null));

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');
        $mapping
                ->expects($this->once())
                ->method('getDeleteOnRemove')
                ->will($this->returnValue(true));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $mapping
                ->expects($this->never())
                ->method('getUploadDir');

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new FileSystemStorage($this->factory);
        $storage->remove($obj);
    }
    /**
     * Test the remove method skips trying to remove a file that no longer exists
     */
    public function testRemoveSkipsNonExistingFile()
    {
        $obj = new DummyEntity();

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue('file.txt'));

        $propertyMock = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $propertyMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(null));


        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');
        $mapping
                ->expects($this->once())
                ->method('getDeleteOnRemove')
                ->will($this->returnValue(true));

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue('/tmp'));

        $mapping
            ->expects($this->any())
            ->method('getProperty')
            ->will($this->returnValue($propertyMock));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $this->factory
                ->expects($this->once())
                ->method('fromObject')
                ->with($obj)
                ->will($this->returnValue(array($mapping)));

        $storage = new FileSystemStorage($this->factory);
        try{
             $storage->remove($obj);
        }
        catch(\Exception $e){
            $this->fail('We tried to remove nonexistent file');
        }
    }

    /**
     * Test the resolve path method.
     */
    public function testResolvePath()
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue('file.txt'));

        $mapping
                ->expects($this->once())
                ->method('getUploadDir')
                ->will($this->returnValue('/tmp'));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $this->factory
                ->expects($this->once())
                ->method('fromField')
                ->with($obj, 'file')
                ->will($this->returnValue($mapping));

        $storage = new FileSystemStorage($this->factory);
        $path = $storage->resolvePath($obj, 'file');

        $this->assertEquals(sprintf('/tmp%sfile.txt', DIRECTORY_SEPARATOR), $path);
    }

    /**
     * Test the resolve uri
     *
     * @dataProvider resolveUriDataProvider
     */
    public function testResolveUri($uploadDir, $uri)
    {
        $obj = new DummyEntity();

        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $prop = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
        $prop
                ->expects($this->once())
                ->method('getValue')
                ->with($obj)
                ->will($this->returnValue('file.txt'));

        $mapping
                ->expects($this->once())
                ->method('getUploadDir')
                ->will($this->returnValue($uploadDir));

        $mapping
                ->expects($this->once())
                ->method('getUriPrefix')
                ->will($this->returnValue('/uploads'));

        $mapping
                ->expects($this->once())
                ->method('getFileNameProperty')
                ->will($this->returnValue($prop));

        $this->factory
                ->expects($this->once())
                ->method('fromField')
                ->with($obj, 'file')
                ->will($this->returnValue($mapping));

        $storage = new FileSystemStorage($this->factory);
        $path = $storage->resolveUri($obj, 'file');

        $this->assertEquals($uri, $path);
    }

    public function resolveUriDataProvider()
    {
        return array(
            array(
                '/abs/path/web/uploads',
                '/uploads/file.txt'
            ),
            array(
                'c:\abs\path\web\uploads',
                '/uploads/file.txt'
            ),
            array(
                '/abs/path/web/project/web/uploads',
                '/uploads/file.txt'
            ),
            array(
                '/abs/path/web/project/web/uploads/custom/dir',
                '/uploads/custom/dir/file.txt'
            ),
        );
    }

    /**
     * Test the resolve path method throws exception
     * when an invaid field name is specified.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testResolvePathThrowsExceptionOnInvalidFieldName()
    {
        $obj = new DummyEntity();

        $this->factory
                ->expects($this->once())
                ->method('fromField')
                ->with($obj, 'oops')
                ->will($this->returnValue(null));

        $storage = new FileSystemStorage($this->factory);
        $storage->resolvePath($obj, 'oops');
    }

    /**
     *  Test that file upload moves uploaded file to correct directory and with correct filename
     */
    public function testUploadedFileIsCorrectlyMoved()
    {
        $obj = new DummyEntity();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $file
            ->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue('filename.txt'));

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $name = new \stdClass();

        $prop
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));


        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
            ->expects($this->any())
            ->method('getProperty')
            ->will($this->returnValue($prop));


        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));

        $mapping
            ->expects($this->once())
            ->method('getPropertyValue')
            ->with($obj)
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->once())
            ->method('getDeleteOnUpdate')
            ->will($this->returnValue(false));

        $mapping
            ->expects($this->once())
            ->method('hasNamer')
            ->will($this->returnValue(false));

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($obj,$name)
            ->will($this->returnValue('/dir'));

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($mapping)));

        $file
            ->expects($this->once())
            ->method('move')
            ->with('/dir', 'filename.txt');

        $storage = new FileSystemStorage($this->factory);
        $storage->upload($obj);

    }


    /**
     *  Test file upload when filename contains directories
     * @dataProvider filenameWithDirectoriesDataProvider
     */
    public function testFilenameWithDirectoriesIsUploadedToCorrectDirectory($dir, $filename, $expectedDir, $expectedFileName)
    {
        $obj = new DummyEntity();

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $name = new \stdClass();

        $namer = $this->getMockBuilder('Vich\UploaderBundle\Naming\NamerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $namer
            ->expects($this->once())
            ->method('name')
            ->with($obj, $name)
            ->will($this->returnValue($filename));

        $prop
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));


        $mapping = $this->getMock('Vich\UploaderBundle\Mapping\PropertyMapping');

        $mapping
            ->expects($this->once())
            ->method('getNamer')
            ->will($this->returnValue($namer));

        $mapping
            ->expects($this->any())
            ->method('getProperty')
            ->will($this->returnValue($prop));

        $mapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($prop));

        $mapping
            ->expects($this->once())
            ->method('getPropertyValue')
            ->with($obj)
            ->will($this->returnValue($file));

        $mapping
            ->expects($this->once())
            ->method('getDeleteOnUpdate')
            ->will($this->returnValue(false));

        $mapping
            ->expects($this->once())
            ->method('hasNamer')
            ->will($this->returnValue(true));

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($obj,$name)
            ->will($this->returnValue($dir));

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($mapping)));

        $file
            ->expects($this->once())
            ->method('move')
            ->with($expectedDir, $expectedFileName);

        $storage = new FileSystemStorage($this->factory);
        $storage->upload($obj);

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
     * Creates a mock factory.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getFactoryMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
                        ->disableOriginalConstructor()
                        ->getMock();
    }

}
