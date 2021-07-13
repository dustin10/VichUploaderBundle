<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\AdvancedMetadataFactoryInterface;
use Vich\UploaderBundle\Exception\MappingNotFoundException;

/**
 * MetadataReader.
 *
 * Exposes a simple interface to read objects metadata.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @final
 *
 * @internal
 */
class MetadataReader
{
    /**
     * @var AdvancedMetadataFactoryInterface
     */
    protected $reader;

    /**
     * Constructs a new instance of the MetadataReader.
     *
     * @param AdvancedMetadataFactoryInterface $reader The "low-level" metadata reader
     */
    public function __construct(AdvancedMetadataFactoryInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Tells if the given class is uploadable.
     *
     * @param string      $class   The class name to test (FQCN)
     * @param string|null $mapping If given, also checks that the object has the given mapping
     *
     * @throws MappingNotFoundException
     */
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

    /**
     * Search for all uploadable classes.
     *
     * @return array|null A list of uploadable class names
     *
     * @throws \RuntimeException
     */
    public function getUploadableClasses(): ?array
    {
        return $this->reader->getAllClassNames();
    }

    /**
     * Attempts to read the uploadable fields.
     *
     * @param string      $class   The class name to test (FQCN)
     * @param string|null $mapping If given, also checks that the object has the given mapping
     *
     * @return array A list of uploadable fields
     *
     * @throws MappingNotFoundException
     */
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
            $uploadableFields = \array_filter($uploadableFields, static function (array $fieldMetadata) use ($mapping): bool {
                return $fieldMetadata['mapping'] === $mapping;
            });
        }

        return $uploadableFields;
    }

    /**
     * Attempts to read the mapping of a specified property.
     *
     * @param string $class The class name to test (FQCN)
     * @param string $field The field
     *
     * @return mixed The field mapping
     *
     * @throws MappingNotFoundException
     */
    public function getUploadableField(string $class, string $field)
    {
        $fieldsMetadata = $this->getUploadableFields($class);

        return $fieldsMetadata[$field] ?? null;
    }
}
