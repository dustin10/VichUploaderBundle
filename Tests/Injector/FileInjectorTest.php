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
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

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
        $this->factory = $this->getMockMappingFactory();
        $this->storage = $this->getMockStorage();
        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'uploads' => array(
                'file.txt'  => 'some content',
                'image.png' => 'some content',
            ),
        ));

        $this->injector = new FileInjector($this->factory, $this->storage);
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
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));
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

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping)));

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($this->equalTo($obj), $this->equalTo($filePropertyName))
            ->will($this->returnValue($this->getUploadDir() . DIRECTORY_SEPARATOR . 'file.txt'));

        $this->injector->injectFiles($obj);
    }

    /**
     * Test inject two files.
     */
    public function testInjectTwoFiles()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\TwoFieldsDummyEntity');

        $fileMapping = $this->getPropertyMappingMock();
        $fileMapping
            ->expects($this->once())
            ->method('getFilePropertyName')
            ->will($this->returnValue('file'));
        $fileMapping
            ->expects($this->once())
            ->method('setFile')
            ->with($this->equalTo($obj), $this->callback(function ($file) {
                return $file instanceof File;
            }));

        $imageMapping = $this->getPropertyMappingMock();
        $imageMapping
            ->expects($this->once())
            ->method('getFilePropertyName')
            ->will($this->returnValue('image'));
        $imageMapping
            ->expects($this->once())
            ->method('setFile')
            ->with($this->equalTo($obj), $this->callback(function ($file) {
                return $file instanceof File;
            }));

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping, $imageMapping)));

        $this->storage
            ->expects($this->exactly(2))
            ->method('resolvePath')
            ->will($this->onConsecutiveCalls(
                $this->getUploadDir() . DIRECTORY_SEPARATOR . 'file.txt',
                $this->getUploadDir() . DIRECTORY_SEPARATOR . 'image.png'
            ));

        $this->injector->injectFiles($obj);
    }

    /**
     * Test injection is skipped if inject_on_load is configured
     * to false.
     */
    public function testInjectionIsSkippedIfNotConfigured()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileMapping = $this->getPropertyMappingMock(false);
        $fileMapping
            ->expects($this->never())
            ->method('setFile');

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping)));

        $this->injector->injectFiles($obj);
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

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping)));

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->will($this->throwException(new \InvalidArgumentException));

        $this->injector->injectFiles($obj);
    }

    /**
     * Gets a mock mapping factory.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getMockMappingFactory()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets a mocked property mapping.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping The property.
     */
    protected function getPropertyMappingMock($inject_on_load_enabled = true)
    {
        $mapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();

        $mapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue($inject_on_load_enabled));

        return $mapping;
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
