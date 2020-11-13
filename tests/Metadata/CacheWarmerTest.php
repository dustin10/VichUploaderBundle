<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\CacheWarmer;
use Vich\UploaderBundle\Metadata\MetadataReader;

final class CacheWarmerTest extends TestCase
{
    public function testWarmUp(): void
    {
        $reader = $this->createMock(MetadataReader::class);
        $reader->expects($this->once())->method('getUploadableClasses')->willReturn([]);

        $warmer = new CacheWarmer(\sys_get_temp_dir(), $reader);
        $warmer->warmUp('foo');
    }

    public function testDoNotWarmUpEmptyDir(): void
    {
        $reader = $this->createMock(MetadataReader::class);
        $reader->expects($this->never())->method('getUploadableClasses');

        $warmer = new CacheWarmer('', $reader);
        $warmer->warmUp('foo');
    }
}
