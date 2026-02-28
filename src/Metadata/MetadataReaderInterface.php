<?php

namespace Vich\UploaderBundle\Metadata;

use Vich\UploaderBundle\Exception\MappingNotFoundException;

interface MetadataReaderInterface
{
    /**
     * Tell if the given class is uploadable.
     *
     * @param string      $class   The class name to test (FQCN)
     * @param string|null $mapping If given, also checks that the object has the given mapping
     *
     * @throws MappingNotFoundException
     */
    public function isUploadable(string $class, ?string $mapping = null): bool;

    /**
     * Search for all uploadable classes.
     *
     * @return array A list of uploadable class names
     *
     * @throws \RuntimeException
     */
    public function getUploadableClasses(): array;

    /**
     * Attempt to read the uploadable fields.
     *
     * @param string      $class   The class name to test (FQCN)
     * @param string|null $mapping If given, also checks that the object has the given mapping
     *
     * @return array A list of uploadable fields
     *
     * @throws MappingNotFoundException
     */
    public function getUploadableFields(string $class, ?string $mapping = null): array;

    /**
     * Attempt to read the mapping of a specified property.
     *
     * @param string $class The class name to test (FQCN)
     * @param string $field The field
     *
     * @return mixed The field mapping
     *
     * @throws MappingNotFoundException
     */
    public function getUploadableField(string $class, string $field): mixed;
}
