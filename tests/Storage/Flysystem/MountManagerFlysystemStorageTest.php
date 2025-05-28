<?php

namespace Vich\UploaderBundle\Tests\Storage\Flysystem;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use PHPUnit\Framework\Attributes\RequiresMethod;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class MountManagerFlysystemStorageTest extends AbstractFlysystemStorageTestCase
{
    #[RequiresMethod(MountManager::class, '__construct')]
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped('need a rewrite for Flysystem v2 and v3');
    }

    protected function createRegistry(FilesystemOperator $filesystem): MountManager
    {
        $mountManager = $this->createMock(MountManager::class);

        // TODO the getFileSystem method was removed from MountManager class in v2
        $mountManager
            ->method('getFilesystem')
            ->with(self::FS_KEY)
            ->willReturn($filesystem);

        return $mountManager;
    }
}
