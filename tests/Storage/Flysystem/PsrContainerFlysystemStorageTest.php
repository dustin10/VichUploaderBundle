<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\FilesystemInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class PsrContainerFlysystemStorageTest extends AbstractFlysystemStorageTest
{
    protected function createRegistry(FilesystemInterface $filesystem)
    {
        $locator = $this
            ->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $locator
            ->expects($this->any())
            ->method('get')
            ->with(self::FS_KEY)
            ->willReturn($filesystem);

        return $locator;
    }
}
