<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\FilesystemOperator;
use Psr\Container\ContainerInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class PsrContainerFlysystemStorageTest extends AbstractFlysystemStorageTestCase
{
    public function testResolveUriWithAbsoluteDirectory(): void
    {
        $this->mapping
            ->expects(self::once())
            ->method('getUriPrefix')
            ->willReturn('');

        $this->mapping
            ->expects(self::once())
            ->method('getUploadDir')
            ->willReturn('/dir');

        $this->mapping
            ->expects(self::once())
            ->method('getFileName')
            ->willReturn('file.txt');

        $this->factory
            ->expects(self::once())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->willReturn($this->mapping);

        $path = $this->getStorage()->resolveUri($this->object, 'file_field');

        self::assertEquals('/dir/file.txt', $path);
    }

    protected function createRegistry(FilesystemOperator $filesystem): ContainerInterface
    {
        $locator = $this->createMock(ContainerInterface::class);

        $locator
            ->method('get')
            ->with(self::FS_KEY)
            ->willReturn($filesystem);

        return $locator;
    }
}
