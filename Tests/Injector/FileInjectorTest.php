<?php

namespace Vich\UploaderBundle\Tests\Injector;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Injector\FileInjector;

/**
 * FileInjectorTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class FileInjectorTest extends TestCase
{
    /**
     * @var Vich\UploaderBundle\Storage\GaufretteStorage
     */
    protected $storage;

    /**
     * Sets up the test.
     */
    protected function setUp()
    {
        $this->storage = $this->getMockStorage();
    }

    /**
     * Test inject one file.
     */
    public function testInjectsOneFile()
    {
        $obj = $this->createMock('Vich\UploaderBundle\Tests\DummyEntity');

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
        $obj = $this->createMock('Vich\UploaderBundle\Tests\DummyEntity');

        $fileMapping = $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();

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
