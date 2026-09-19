<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\Metadata\CacheWarmer;
use Vich\UploaderBundle\Tests\TestCase;

final class CacheWarmerTest extends TestCase
{
    #[Test]
    public function warmUp(): void
    {
        $reader = $this->getMetadataReaderMock();
        $reader->expects($this->once())->method('getUploadableClasses')->willReturn([]);

        $warmer = new CacheWarmer(\sys_get_temp_dir(), $reader);
        $warmer->warmUp('foo');
    }

    #[Test]
    public function doNotWarmUpEmptyDir(): void
    {
        $reader = $this->getMetadataReaderMock();
        $reader->expects($this->never())->method('getUploadableClasses');

        $warmer = new CacheWarmer('', $reader);
        $warmer->warmUp('foo');
    }
}
