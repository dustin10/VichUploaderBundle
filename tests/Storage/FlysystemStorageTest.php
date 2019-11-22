<?php

namespace Vich\UploaderBundle\Tests\Storage;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorageTest extends StorageTestCase
{
    public const FS_KEY = 'filesystemKey';

    /**
     * @var MountManager&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mountManager;

    /**
     * @var Filesystem&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystem;

    /**
     * @var AbstractAdapter&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $adapter;

    public static function setUpBeforeClass(): void
    {
        if (!\class_exists(MountManager::class)) {
            self::markTestSkipped('Flysystem is not installed.');
        }
    }

    protected function getStorage(): StorageInterface
    {
        return new FlysystemStorage($this->factory, $this->mountManager);
    }

    protected function setUp(): void
    {
        $this->mountManager = $this->getMountManagerMock();
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->adapter = $this->createMock(AbstractAdapter::class);

        $this->mountManager
            ->expects($this->any())
            ->method('getFilesystem')
            ->with(self::FS_KEY)
            ->willReturn($this->filesystem);

        parent::setUp();

        $this->mapping
            ->expects($this->any())
            ->method('getUploadDestination')
            ->willReturn(self::FS_KEY);
    }

    public function testUpload(): void
    {
        $file = $this->getUploadedFileMock();

        $file
            ->expects($this->once())
            ->method('getRealPath')
            ->willReturn($this->root->url().\DIRECTORY_SEPARATOR.'uploads'.\DIRECTORY_SEPARATOR.'test.txt');

        $file
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('originalName.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->willReturn($file);

        $this->mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($this->object)
            ->willReturn('originalName.txt');

        $this->filesystem
            ->expects($this->once())
            ->method('putStream')
            ->with(
                'originalName.txt',
                $this->isType('resource'),
                $this->isType('array')
            );

        $this->storage->upload($this->object, $this->mapping);
    }

    public function testRemove(): void
    {
        $this->filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('test.txt');

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('test.txt');

        $this->storage->remove($this->object, $this->mapping);
    }

    public function testRemoveOnNonExistentFile(): void
    {
        $this->filesystem
            ->expects($this->once())
            ->method('delete')
            ->with('not_found.txt')
            ->will($this->throwException(new FileNotFoundException('dummy path')));

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('not_found.txt');

        $this->storage->remove($this->object, $this->mapping);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testResolvePath(?string $uploadDir, string $expectedPath, bool $relative): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->willReturn($uploadDir);

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects($this->once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $this->filesystem
            ->expects($this->any())
            ->method('getAdapter')
            ->willReturn($this->adapter);

        $this->adapter
            ->expects($this->any())
            ->method('applyPathPrefix')
            ->willReturn($uploadDir ? '/absolute/'.$uploadDir.'/file.txt' : '/absolute/file.txt');

        $path = $this->storage->resolvePath($this->object, 'file_field', null, $relative);

        $this->assertEquals($expectedPath, $path);
    }

    public function pathProvider(): array
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
     * @return MountManager&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMountManagerMock()
    {
        return $this
            ->getMockBuilder(MountManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
