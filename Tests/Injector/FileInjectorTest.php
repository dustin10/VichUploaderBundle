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
     * @var Vich\UploaderBundle\Storage\GaufretteStorage $storage
     */
    protected $storage;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getMockMappingFactory();
        $this->storage = $this->getMockStorage();
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

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $fileMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $fileMapping
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->will($this->returnValue($prop));


        $mappings = array($fileMapping);

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue($mappings));

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->will($this->returnValue($uploadDir));

        $inject = new FileInjector($this->factory, $this->storage);
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

        $imageProp = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $imageProp
            ->expects($this->once())
            ->method('setValue');

        $imageNameProp = $this->getMockBuilder('\ReflectionProperty')
                         ->disableOriginalConstructor()
                         ->getMock();
        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $fileMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $fileMapping
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->will($this->returnValue($fileProp));

        $imageMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
                       ->disableOriginalConstructor()
                       ->getMock();

        $imageMapping
            ->expects($this->once())
            ->method('getInjectOnLoad')
            ->will($this->returnValue(true));

        $imageMapping
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->will($this->returnValue($imageProp));

        $mappings = array($fileMapping, $imageMapping);

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue($mappings));

        $this->storage
            ->expects($this->exactly(2))
            ->method('resolvePath')
            ->will($this->returnValue($uploadDir));

        $inject = new FileInjector($this->factory, $this->storage);
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

        $inject = new FileInjector($this->factory, $this->storage);
        $inject->injectFiles($obj);

        $this->assertEquals(null, $obj->getFile());
    }

    /**
     * Test that if the file name property returns a null value
     * then no file is injected.
     */
    public function testPropertyIsNullWhenFileNamePropertyIsNull()
    {
        $uploadDir = __DIR__ . '/..';

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
            ->will($this->returnValue(true));

        $prop = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $prop
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_adapter'));

        $prop
            ->expects($this->once())
            ->method('setValue');

        $fileMapping
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->will($this->returnValue($prop));

        $this->factory
            ->expects($this->once())
            ->method('fromObject')
            ->with($obj)
            ->will($this->returnValue(array($fileMapping)));

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->will($this->returnValue($uploadDir));

        $inject = new FileInjector($this->factory, $this->storage);
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

    /**
     * Gets a mock storage.
     *
     * @return Vich\UploaderBundle\Storage\GaufretteStorage Storage
     */
    protected function getMockStorage()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Storage\GaufretteStorage')
               ->disableOriginalConstructor()
               ->getMock()
        ;
    }
}
