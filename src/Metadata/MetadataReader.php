<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\AdvancedMetadataFactoryInterface;
use Vich\UploaderBundle\Exception\MappingNotFoundException;

/**
 * Expose a simple interface to read objects metadata.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @internal
 */
final readonly class MetadataReader implements MetadataReaderInterface
{
    /**
     * Constructs a new instance of the MetadataReader.
     *
     * @param AdvancedMetadataFactoryInterface $reader The "low-level" metadata reader
     */
    public function __construct(private AdvancedMetadataFactoryInterface $reader)
    {
    }

    public function isUploadable(string $class, ?string $mapping = null): bool
    {
        $metadata = $this->reader->getMetadataForClass($class);

        if (null === $metadata) {
            return false;
        }

        if (null === $mapping) {
            return true;
        }

        foreach ($this->getUploadableFields($class) as $fieldMetadata) {
            if ($fieldMetadata['mapping'] === $mapping) {
                return true;
            }
        }

        return false;
    }

    public function getUploadableClasses(): array
    {
        return $this->reader->getAllClassNames();
    }

    public function getUploadableFields(string $class, ?string $mapping = null): array
    {
        if (null === $metadata = $this->reader->getMetadataForClass($class)) {
            throw MappingNotFoundException::createNotFoundForClass($mapping ?? '', $class);
        }
        $uploadableFields = [];

        /** @var ClassMetadata $classMetadata */
        foreach ($metadata->classMetadata as $classMetadata) {
            $uploadableFields = \array_merge($uploadableFields, $classMetadata->fields);
        }

        if (null !== $mapping) {
            $uploadableFields = \array_filter($uploadableFields, static fn (array $fieldMetadata): bool => $fieldMetadata['mapping'] === $mapping);
        }

        return $uploadableFields;
    }

    public function getUploadableField(string $class, string $field): mixed
    {
        $fieldsMetadata = $this->getUploadableFields($class);

        return $fieldsMetadata[$field] ?? null;
    }
}
