<?php

namespace Vich\UploaderBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Vich\UploaderBundle\Metadata\MetadataReader;

/**
 * @internal
 */
final class MappingCollector extends DataCollector
{
    public function __construct(private readonly MetadataReader $metadataReader)
    {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
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
        return \is_countable($this->data['mappings']) ? \count($this->data['mappings']) : 0;
    }

    public function getMappings(): array
    {
        return $this->data['mappings'];
    }
}
