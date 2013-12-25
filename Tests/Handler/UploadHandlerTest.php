<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\UploaderBundle\Injector\FileInjectorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * UploadHandler test case.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileInjectorInterface
     */
    protected $injector;

    /**
     * @var PropertyMappingFactory
     */
    protected $factory;

    /**
     * @var UploadHandler
     */
    protected $storage;

    /**
     * @var UploadHandler
     */
    protected $handler;

    /**
     * @var DummyEntity
     */
    protected $object;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->injector = $this->getFileInjectorMock();
        $this->storage = $this->getStorageMock();
        $this->object = new DummyEntity();

        $this->handler = new UploadHandler($this->factory, $this->storage, $this->injector);
    }

    public function testHandleUploadDoesNothingIfMappingDoesntExistForObject()
    {
        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(false));

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $this->handler->handleUpload($this->object, 'dummy_mapping');
    }

    /**
     * @dataProvider invalidUploadedFileProvider
     */
    public function testHandleUploadDoesNothingIfNoFileIsUploaded($file)
    {
        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $this->storage
            ->expects($this->never())
            ->method('upload');

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $this->handler->handleUpload($this->object, 'dummy_mapping');
    }

    public function testHandleUpload()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $this->storage
            ->expects($this->once())
            ->method('upload')
            ->with($this->object, $mapping);

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($this->object, $mapping);

        $this->handler->handleUpload($this->object, 'dummy_mapping');
    }

    public function testHandleCleaningDoesNothingIfMappingDoesntExistForObject()
    {
        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(false));

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $this->handler->handleCleaning($this->object, 'dummy_mapping');
    }

    public function testHandleCleaningDoesNothingIfNoOldFileIsPresent()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));
        $mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $this->handler->handleCleaning($this->object, 'dummy_mapping');
    }

    /**
     * @dataProvider invalidUploadedFileProvider
     */
    public function testHandleCleaningDoesNothingIfNoFileIsUploaded($file)
    {
        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $this->storage
            ->expects($this->never())
            ->method('remove');

        $this->handler->handleCleaning($this->object, 'dummy_mapping');
    }

    public function invalidUploadedFileProvider()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(null),
            array('lala'),
            array(new \DateTime()),
            array($file),
        );
    }

    public function testHandleCleaning()
    {
        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));
        $mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue('foo.txt'));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $mapping);

        $this->handler->handleCleaning($this->object, 'dummy_mapping');
    }

    public function testHandleHydrationDoesNothingIfMappingDoesntExistForObject()
    {
        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(false));

        $this->injector
            ->expects($this->never())
            ->method('injectFiles');

        $this->handler->handleHydration($this->object, 'dummy_mapping');
    }

    public function testHandleHydration()
    {
        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $this->injector
            ->expects($this->once())
            ->method('injectFiles')
            ->with($this->object, $mapping);

        $this->handler->handleHydration($this->object, 'dummy_mapping');
    }

    public function testHandleDeletionDoesNothingIfMappingDoesntExistForObject()
    {
        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(false));

        $this->injector
            ->expects($this->never())
            ->method('remove');

        $this->handler->handleDeletion($this->object, 'dummy_mapping');
    }

    public function testHandleDeletion()
    {
        $mapping = $this->getPropertyMappingMock();

        $this->factory
            ->expects($this->once())
            ->method('hasMapping')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue(true));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'dummy_mapping')
            ->will($this->returnValue($mapping));

        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with($this->object, $mapping);

        $this->handler->handleDeletion($this->object, 'dummy_mapping');
    }

    /**
     * Creates a mock property mapping factory
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMappingFactory
     */
    protected function getPropertyMappingFactoryMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets a mock storage.
     *
     * @return \Vich\UploaderBundle\Storage\StorageInterface
     */
    protected function getStorageMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Storage\StorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets a mock file injector.
     *
     * @return \Vich\UploaderBundle\Injector\FileInjectorInterface
     */
    protected function getFileInjectorMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Injector\FileInjectorInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Gets a mock property mapping.
     *
     * @return \Vich\UploaderBundle\Mapping\PropertyMapping
     */
    protected function getPropertyMappingMock()
    {
        return $this->getMockBuilder('Vich\UploaderBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
