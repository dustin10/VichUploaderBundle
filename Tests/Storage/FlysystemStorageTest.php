<?php

namespace Vich\UploaderBundle\Tests\Storage;

use League\Flysystem\File;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Vich\UploaderBundle\Storage\FlysystemStorage;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorageTest extends StorageTestCase
{
    const FS_KEY = 'filesystemKey';

    /**
     * @var MountManager
     */
    protected $mountManager;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public static function setUpBeforeClass()
    {
        if (!class_exists('League\Flysystem\MountManager')) {
            self::markTestSkipped('Flysystem is not installed.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getStorage()
    {
        return new FlysystemStorage($this->factory, $this->mountManager);
    }

    /**
     * Sets up the test.
     */
    protected function setUp()
    {
        $this->mountManager = $this->getMountManagerMock();
        $this->filesystem = $this->createMock('League\Flysystem\FilesystemInterface');

        $this->mountManager
            ->expects($this->any())
            ->method('getFilesystem')
            ->with(self::FS_KEY)
            ->will($this->returnValue($this->filesystem));

        parent::setUp();

        $this->mapping
            ->expects($this->any())
            ->method('getUploadDestination')
            ->will($this->returnValue(self::FS_KEY));
    }

    public function testUpload()
    {
        $file = $this->getUploadedFileMock();

        $file
            ->expects($this->once())
            ->method('getRealPath')
            ->will($this->returnValue($this->root->url().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'test.txt'));

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
            ->method('getUploadName')
            ->with($this->object)
            ->will($this->returnValue('originalName.txt'));

        $this->filesystem
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
        $this->filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('test.txt'));

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testRemoveOnNonExistentFile()
    {
        $this->filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('not_found.txt')
            ->will($this->throwException(new FileNotFoundException('dummy path')));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('not_found.txt'));

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testResolvePath($uploadDir, $expectedPath, $relative)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnValue('file.txt'));

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));

        $this->filesystem
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(
                new File(
                    $this->filesystem,
                    $uploadDir ? '/absolute/'.$uploadDir.'/file.txt' : '/absolute/file.txt'
                )
            ));

        $path = $this->storage->resolvePath($this->object, 'file_field', null, $relative);

        $this->assertEquals($expectedPath, $path);
    }

    public function pathProvider()
    {
        return [
            //     dir,   path,                     relative
            [null,  'file.txt',               true],
            [null,  '/absolute/file.txt',     false],
            ['foo', 'foo/file.txt',           true],
            ['foo', '/absolute/foo/file.txt', false],
        ];
    }

    /**
     * Creates a filesystem map mock.
     *
     * @return MountManager The mount manager
     */
    protected function getMountManagerMock()
    {
        return $this
            ->getMockBuilder('League\Flysystem\MountManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
