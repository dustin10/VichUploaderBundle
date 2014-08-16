<?php

namespace Vich\UploaderBundle\Tests\Storage;

use Vich\UploaderBundle\Storage\FlysystemStorage;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorageTest extends StorageTestCase
{
    /**
     * @var \League\Flysystem\MountManager $mountManager
     */
    protected $mountManager;

    /**
     * {@inheritDoc}
     */
    protected function getStorage()
    {
        return new FlysystemStorage($this->factory, $this->mountManager);
    }

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->mountManager = $this->getMountManagerMock();

        parent::setUp();
    }

    public function testUpload()
    {
        $file = $this->getUploadedFileMock();
        $filesystem = $this->getFilesystemMock();

        $file
            ->expects($this->once())
            ->method('getRealPath')
            ->will($this->returnValue($this->root->url() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'test.txt'));
        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('originalName.txt'));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue($file));
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('filesystemKey'));

        $this->mountManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->with('filesystemKey')
            ->will($this->returnValue($filesystem));

        $filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with(
                'originalName.txt',
                $this->isType('resource'),
                $this->isType('array')
            );

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testRemove()
    {
        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('test.txt');

        $this->mountManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->with('dir')
            ->will($this->returnValue($filesystem));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('dir'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('test.txt'));

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testRemoveOnNonExistentFile()
    {
        $filesystem = $this->getFilesystemMock();
        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('not_found.txt')
            ->will($this->throwException(new \League\Flysystem\FileNotFoundException('dummy path')));

        $this->mountManager
            ->expects($this->once())
            ->method('getFilesystem')
            ->with('dir')
            ->will($this->returnValue($filesystem));

        $this->mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->will($this->returnValue('dir'));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('not_found.txt'));

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * Creates a filesystem map mock.
     *
     * @return \League\Flysystem\MountManager The mount manager.
     */
    protected function getMountManagerMock()
    {
        return $this
            ->getMockBuilder('League\Flysystem\MountManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a filesystem mock.
     *
     * @return \League\Flysystem\FilesystemInterface The filesystem object.
     */
    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('League\Flysystem\FilesystemInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
