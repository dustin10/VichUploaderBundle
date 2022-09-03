<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\FilesystemOperator;
use Psr\Container\ContainerInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class PsrContainerFlysystemStorageTest extends AbstractFlysystemStorageTest
{
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
