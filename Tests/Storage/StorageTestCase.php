<?php

namespace Vich\UploaderBundle\Tests\Storage;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\HttpFoundation\File\File;

use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * Common tests for all storage implementations.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class StorageTestCase extends TestCase
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
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    /**
     * Returns the storage implementation to test.
     *
     * @return StorageInterface
     */
    abstract protected function getStorage();

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->factory = $this->getFactoryMock();
        $this->mapping = $this->getMappingMock();
        $this->object = new DummyEntity();
        $this->storage = $this->getStorage();

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

    public function invalidFileProvider()
    {
        $file = new File('dummy.file', false);

        return array(
            // skipped because null
            array( null ),
            // skipped because not even a file
            array( new \DateTime() ),
            // skipped because not instance of UploadedFile
            array( $file ),
        );
    }

    public function emptyFilenameProvider()
    {
        return array(
            array( null ),
            array( '' ),
        );
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolvePathWithEmptyFile($filename)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($filename));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'file_mapping')
            ->will($this->returnValue($this->mapping));

        $this->assertNull($this->storage->resolvePath($this->object, 'file_mapping'));
    }

    /**
     * @dataProvider emptyFilenameProvider
     */
    public function testResolveUriWithEmptyFile($filename)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue($filename));

        $this->factory
            ->expects($this->once())
            ->method('fromName')
            ->with($this->object, 'file_mapping')
            ->will($this->returnValue($this->mapping));

        $this->assertNull($this->storage->resolvePath($this->object, 'file_mapping'));
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
