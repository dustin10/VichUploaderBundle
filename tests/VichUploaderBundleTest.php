<?php

namespace Vich\UploaderBundle\Tests;

use League\Flysystem\FilesystemOperator;
use Oneup\FlysystemBundle\OneupFlysystemBundle;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Tests\Kernel\FilesystemAppKernel;
use Vich\UploaderBundle\Tests\Kernel\FlysystemOfficialAppKernel;
use Vich\UploaderBundle\Tests\Kernel\FlysystemOneUpAppKernel;
use Vich\UploaderBundle\Tests\Kernel\SimpleAppKernel;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class VichUploaderBundleTest extends TestCase
{
    public function testSimpleKernel(): void
    {
        $kernel = new SimpleAppKernel('test', true);
        $kernel->boot();

        self::assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());
        self::assertInstanceOf(UploadHandler::class, $kernel->getContainer()->get('vich_uploader.upload_handler'));
    }

    public function testFilesystemKernel(): void
    {
        $kernel = new FilesystemAppKernel('test', true);
        $kernel->boot();

        self::assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());
        self::assertInstanceOf(UploadHandler::class, $kernel->getContainer()->get('vich_uploader.upload_handler'));
    }

    public function testFlysystemOfficialKernel(): void
    {
        $kernel = new FlysystemOfficialAppKernel('test', true);
        $kernel->boot();

        self::assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());

        // Test the upload
        /** @var FilesystemOperator $filesystem */
        $filesystem = $kernel->getContainer()->get('test.uploads.storage');
        self::assertFalse($filesystem->fileExists('filename.txt'));

        /** @var FlysystemStorage $storage */
        $storage = $kernel->getContainer()->get('test.vich_uploader.storage');
        self::assertInstanceOf(FlysystemStorage::class, $storage);

        $object = new DummyEntity();

        $mapping = $this->getPropertyMappingMock();

        $mapping
            ->expects(self::once())
            ->method('getFile')
            ->with($object)
            ->willReturn($this->createUploadedFile());

        $mapping
            ->expects(self::once())
            ->method('getUploadDestination')
            ->willReturn('uploads.storage');

        $mapping
            ->expects(self::once())
            ->method('getUploadName')
            ->with($object)
            ->willReturn('filename.txt');

        $mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->with($object)
            ->willReturn('');

        $storage->upload($object, $mapping);

        /** @var FilesystemOperator $filesystem */
        $filesystem = $kernel->getContainer()->get('test.uploads.storage');
        self::assertTrue($filesystem->fileExists('filename.txt'));
    }

    public function testFlysystemOneUpKernel(): void
    {
        if (!\class_exists(OneupFlysystemBundle::class)) {
            $this->markTestSkipped('OneupFlysystemBundle supports only PHP > 7.4');
        }

        $kernel = new FlysystemOneUpAppKernel('test', true);
        $kernel->boot();

        self::assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());

        // Test the upload
        /** @var FilesystemOperator $filesystem */
        $filesystem = $kernel->getContainer()->get('oneup_flysystem.product_image_fs_filesystem');
        self::assertFalse($filesystem->fileExists('filename.txt'));

        /** @var FlysystemStorage $storage */
        $storage = $kernel->getContainer()->get('test.vich_uploader.storage');
        self::assertInstanceOf(FlysystemStorage::class, $storage);

        $object = new DummyEntity();

        $mapping = $this->getPropertyMappingMock();

        $mapping
            ->expects(self::once())
            ->method('getFile')
            ->with($object)
            ->willReturn($this->createUploadedFile());

        $mapping
            ->expects(self::once())
            ->method('getUploadDestination')
            ->willReturn('oneup_flysystem.product_image_fs_filesystem');

        $mapping
            ->expects(self::once())
            ->method('getUploadName')
            ->with($object)
            ->willReturn('filename.txt');

        $mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->with($object)
            ->willReturn('');

        $storage->upload($object, $mapping);

        /** @var FilesystemOperator $filesystem */
        $filesystem = $kernel->getContainer()->get('oneup_flysystem.product_image_fs_filesystem');
        self::assertTrue($filesystem->fileExists('filename.txt'));
    }

    public function testReplacingFileIsCorrectlyUploaded(): void
    {
        $kernel = new FlysystemOfficialAppKernel('test', true);
        $kernel->boot();

        self::assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());

        // Test the upload
        /** @var FilesystemOperator $filesystem */
        $filesystem = $kernel->getContainer()->get('test.uploads.storage');
        self::assertFalse($filesystem->fileExists('filename.txt'));

        /** @var FlysystemStorage $storage */
        $storage = $kernel->getContainer()->get('test.vich_uploader.storage');
        self::assertInstanceOf(FlysystemStorage::class, $storage);

        $object = new DummyEntity();

        $mapping = $this->getPropertyMappingMock();

        $mapping
            ->expects(self::once())
            ->method('getFile')
            ->with($object)
            ->willReturn($this->createReplacingFile());

        $mapping
            ->expects(self::once())
            ->method('getUploadDestination')
            ->willReturn('uploads.storage');

        $mapping
            ->expects(self::once())
            ->method('getUploadName')
            ->with($object)
            ->willReturn('filename.txt');

        $mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->with($object)
            ->willReturn('');

        $storage->upload($object, $mapping);

        /** @var FilesystemOperator $filesystem */
        $filesystem = $kernel->getContainer()->get('test.uploads.storage');
        self::assertTrue($filesystem->fileExists('filename.txt'));
    }

    private function createUploadedFile(): UploadedFile
    {
        return new UploadedFile(
            __DIR__.'/Fixtures/App/app/Resources/images/symfony_black_03.png',
            'symfony_black_03.png',
            null,
            null,
            true
        );
    }

    private function createReplacingFile(): ReplacingFile
    {
        return new ReplacingFile(
            __DIR__.'/Fixtures/App/app/Resources/images/symfony_black_03.png',
        );
    }
}
