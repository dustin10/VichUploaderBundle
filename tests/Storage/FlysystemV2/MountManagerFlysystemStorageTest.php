<?php

namespace Vich\UploaderBundle\Tests\Storage\FlysystemV2;

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
        $mountManager = $this
            ->getMockBuilder(MountManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mountManager
            ->method('getFilesystem')
            ->with(self::FS_KEY)
            ->willReturn($filesystem);

        return $mountManager;
    }
}
