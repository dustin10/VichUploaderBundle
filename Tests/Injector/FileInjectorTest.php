<?php

namespace Vich\UploaderBundle\Tests\Injector;

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
     * @var Vich\UploaderBundle\Storage\GaufretteStorage $storage
     */
    protected $storage;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->storage = $this->getMockStorage();
    }

    /**
     * Test inject one file.
     */
    public function testInjectsOneFile()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
        $fileMapping
            ->expects($this->once())
            ->method('getFilePropertyName')
            ->will($this->returnValue('file_field'));
        $fileMapping
            ->expects($this->once())
            ->method('setFile');

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->with($obj, 'file_field')
            ->will($this->returnValue('/uploadDir/file.txt'));

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
    }

    /**
     * Test that if the file name property returns a null value
     * then no file is injected.
     */
    public function testPropertyIsNullWhenFileNamePropertyIsNull()
    {
        $obj = $this->getMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();

        $fileMapping
            ->expects($this->never())
            ->method('setValue');

        $this->storage
            ->expects($this->once())
            ->method('resolvePath')
            ->will($this->returnValue(null));

        $inject = new FileInjector($this->storage);
        $inject->injectFile($obj, $fileMapping);
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
            ->getMock();
    }
}
