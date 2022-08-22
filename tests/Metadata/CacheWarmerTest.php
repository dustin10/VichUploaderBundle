<?php

namespace Vich\UploaderBundle\Tests\Metadata;

use Vich\UploaderBundle\Metadata\CacheWarmer;
use Vich\UploaderBundle\Tests\TestCase;

final class CacheWarmerTest extends TestCase
{
    public function testWarmUp(): void
    {
        $reader = $this->getMetadataReaderMock();
        $reader->expects(self::once())->method('getUploadableClasses')->willReturn([]);

        $warmer = new CacheWarmer(\sys_get_temp_dir(), $reader);
        $warmer->warmUp('foo');
    }

    public function testDoNotWarmUpEmptyDir(): void
    {
        $reader = $this->getMetadataReaderMock();
        $reader->expects($this->never())->method('getUploadableClasses');

        $warmer = new CacheWarmer('', $reader);
        $warmer->warmUp('foo');
    }
}
