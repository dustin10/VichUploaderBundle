<?php

namespace Vich\UploaderBundle\Tests\DataCollector;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\DataCollector\MappingCollector;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Tests\DummyEntity;

final class MappingCollectorTest extends TestCase
{
    public function testResetClearsCollectedMappings(): void
    {
        $metadataReader = $this->createMock(MetadataReader::class);
        $metadataReader
            ->method('getUploadableClasses')
            ->willReturn([DummyEntity::class]);
        $metadataReader
            ->method('getUploadableFields')
            ->willReturn(['fileName' => 'field-metadata']);

        $collector = new MappingCollector($metadataReader);
        $collector->collect($this->createRequestMock(), $this->createResponseMock());

        self::assertSame(1, $collector->getMappingsCount());

        $collector->reset();

        self::assertSame(0, $collector->getMappingsCount());
        self::assertSame([], $collector->getMappings());
    }

    private function createRequestMock(): \Symfony\Component\HttpFoundation\Request
    {
        return $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
    }

    private function createResponseMock(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->createMock(\Symfony\Component\HttpFoundation\Response::class);
    }
}
