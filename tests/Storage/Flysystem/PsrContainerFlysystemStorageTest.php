<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
#[AllowMockObjectsWithoutExpectations]
final class PsrContainerFlysystemStorageTest extends AbstractFlysystemStorageTestCase
{
    #[Test]
    public function resolveUriWithAbsoluteDirectory(): void
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
        $locator = $this->createStub(ContainerInterface::class);

        $locator
            ->method('get')
            ->willReturnMap([[self::FS_KEY, $filesystem]]);

        return $locator;
    }
}
