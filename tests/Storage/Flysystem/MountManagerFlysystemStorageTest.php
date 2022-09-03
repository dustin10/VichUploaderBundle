<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class MountManagerFlysystemStorageTest extends AbstractFlysystemStorageTest
{
    protected function createRegistry(FilesystemOperator $filesystem): MountManager
    {
        $mountManager = $this->createMock(MountManager::class);

        $mountManager
            ->method('getFilesystem')
            ->with(self::FS_KEY)
            ->willReturn($filesystem);

        return $mountManager;
    }
}
