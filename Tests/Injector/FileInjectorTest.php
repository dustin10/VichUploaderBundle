<?php

namespace Vich\UploaderBundle\Tests\Injector;

use Vich\UploaderBundle\Injector\FileInjector;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * FileInjectorTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getMockMappingFactory();
    }

    /**
     * Test inject one file.
     */
    public function testInjectsOneFile()
    {
        $uploadDir = __DIR__ . '/..';
        $name = 'file.txt';

        file_put_contents(sprintf('%s/%s', $uploadDir, $name), '');

        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $prop
            ->expects($this->once())
            ->method('setValue');

        $fileNameProp = $this->getMockBuilder('\ReflectionProperty')
                        ->disableOriginalConstructor()
                        ->getMock();
        $fileNameProp
            ->expects($this->once())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue($name));

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $fileMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $fileMapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($prop));

        $fileMapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($fileNameProp));

        $fileMapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $mappings = array($fileMapping);

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue($mappings));

        $inject = new FileInjector($this->factory);
        $inject->injectFiles($obj);

        unlink(sprintf('%s/%s', $uploadDir, $name));
    }

    /**
     * Test inject two files.
     */
    public function testInjectTwoFiles()
    {
        $uploadDir = __DIR__ . '/..';
        $fileName = 'file.txt';
        $imageName = 'image.txt';

        file_put_contents(sprintf('%s/%s', $uploadDir, $fileName), '');
        file_put_contents(sprintf('%s/%s', $uploadDir, $imageName), '');

        $obj = $this->getMock('Vich\UploaderBundle\Tests\TwoFieldsDummyEntity');

        $fileProp = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $fileProp
            ->expects($this->once())
            ->method('setValue');

        $fileNameProp = $this->getMockBuilder('\ReflectionProperty')
                        ->disableOriginalConstructor()
                        ->getMock();
        $fileNameProp
            ->expects($this->once())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue($fileName));

        $imageProp = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $imageProp
            ->expects($this->once())
            ->method('setValue');

        $imageNameProp = $this->getMockBuilder('\ReflectionProperty')
                         ->disableOriginalConstructor()
                         ->getMock();
        $imageNameProp
            ->expects($this->once())
            ->method('getValue')
            ->with($obj)
            ->will($this->returnValue($imageName));

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $fileMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $fileMapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($fileProp));

        $fileMapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($fileNameProp));

        $fileMapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $imageMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $imageMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $imageMapping
            ->expects($this->once())
            ->method('getProperty')
            ->will($this->returnValue($imageProp));

        $imageMapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($imageNameProp));

        $imageMapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $mappings = array($fileMapping, $imageMapping);

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue($mappings));

        $inject = new FileInjector($this->factory);
        $inject->injectFiles($obj);

        unlink(sprintf('%s/%s', $uploadDir, $fileName));
        unlink(sprintf('%s/%s', $uploadDir, $imageName));
    }

    /**
     * Test injection is skipped if inject_on_load is configured
     * to false.
     */
    public function testInjectionIsSkippedIfNotConfigured()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileNameProp = $this->getMockBuilder('\ReflectionProperty')
                        ->disableOriginalConstructor()
                        ->getMock();

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $fileMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(false));

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping)));

        $inject = new FileInjector($this->factory);
        $inject->injectFiles($obj);

        $this->assertEquals(null, $obj->getFile());
    }

    /**
     * Test that if the file name property returns a null value
     * then no file is injected.
     */
    public function testPropertyIsNullWhenFileNamePropertyIsNull()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileNameProp = $this->getMockBuilder('\ReflectionProperty')
                        ->disableOriginalConstructor()
                        ->getMock();
        $fileNameProp
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(null));

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $fileMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $fileMapping
            ->expects($this->once())
            ->method('getFileNameProperty')
            ->will($this->returnValue($fileNameProp));

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping)));

        $inject = new FileInjector($this->factory);
        $inject->injectFiles($obj);

        $this->assertEquals(null, $obj->getFile());
    }

    /**
     * Gets a mock mapping factory.
     *
     * @return Vich\UploaderBundle\Mapping\PropertyMappingFactory The factory.
     */
    protected function getMockMappingFactory()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
               ->disableOriginalConstructor()
               ->getMock();
    }
}