<?php

namespace Vich\UploaderBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Vich\UploaderBundle\Metadata\MetadataReader;

final class MappingCollector extends DataCollector
{
    /**
     * @var MetadataReader
     */
    private $metadataReader;

    public function __construct(MetadataReader $metadataReader)
    {
        $this->metadataReader = $metadataReader;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        $mappings = [];
        $uploadableClasses = $this->metadataReader->getUploadableClasses();
        foreach ($uploadableClasses as $class) {
            $mappings[$class] = $this->metadataReader->getUploadableFields($class);
        }

        \ksort($mappings);

        $this->data = [
            'mappings' => $mappings,
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'vich_uploader.mapping_collector';
    }

    public function getMappingsCount(): int
    {
        return \count($this->data['mappings']);
    }

    public function getMappings(): array
    {
        return $this->data['mappings'];
    }
}
