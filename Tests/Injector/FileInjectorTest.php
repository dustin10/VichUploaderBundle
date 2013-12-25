<?php

namespace Vich\UploaderBundle\Tests\Injector;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\HttpFoundation\File\File;

use Vich\UploaderBundle\Injector\FileInjector;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * FileInjectorTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

    /**
     * @var FileInjector
     */
    protected $injector;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->storage = $this->getMockStorage();
        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'uploads' => array(
                'file.txt'  => 'some content',
                'image.png' => 'some content',
            ),
        ));

        $this->injector = new FileInjector($this->storage);
    }

    /**
     * Test inject one file.
     */
    public function testInjectsOneFile()
    {
        $filePropertyName = 'file';
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping
            ->expects($this->once())
            ->method('getFilePropertyName')
            ->will($this->returnValue($filePropertyName));
        $fileMapping
            ->expects($this->once())
            ->method('setFile')
            ->with($this->equalTo($obj), $this->callback(function ($file) {
                return $file instanceof File;
            }));

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($this->equalTo($obj), $this->equalTo($filePropertyName))
            ->will($this->returnValue($this->getUploadDir() . DIRECTORY_SEPARATOR . 'file.txt'));

        $this->injector->injectFiles($obj, $fileMapping);
    }

    public function testPropertyIsNullWhenFileNamePropertyIsNull()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping
            ->expects($this->once())
            ->method('getFilePropertyName')
            ->will($this->returnValue('file'));
        $fileMapping
            ->expects($this->never())
            ->method('setFile');

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->will($this->throwException(new \InvalidArgumentException));

        $this->injector->injectFiles($obj, $fileMapping);
    }

    /**
     * Gets a mocked property mapping.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping The property.
     */
    protected function getPropertyMappingMock($injectOnLoadEnabled = true)
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets a mock storage.
     *
     * @return \Vich\UploaderBundle\Storage\StorageInterface
     */
    protected function getMockStorage()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Storage\StorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUploadDir()
    {
        return $this->root->url() . DIRECTORY_SEPARATOR . 'uploads';
    }
}
