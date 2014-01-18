<?php

namespace Vich\UploaderBundle\Mapping;

use Metadata\MetadataFactoryInterface;

/**
 * MappingReader
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class MappingReader
{
    /**
     * @var AgnosticReader $reader
     */
    protected $reader;

    /**
     * Constructs a new instance of the MappingReader.
     *
     * @param MetadataFactoryInterface $reader The metadata reader.
     */
    public function __construct(MetadataFactoryInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Tells if the given class is uploadable.
     *
     * @param string $class The class name to test (FQCN).
     *
     * @return bool
     */
    public function isUploadable($class)
    {
        $metadata = $this->reader->getMetadataForClass($class);

        return $metadata !== null;
    }

    /**
     * Attempts to read the uploadable fields.
     *
     * @param string $class The class name to test (FQCN).
     *
     * @return array A list of uploadable fields.
     */
    public function getUploadableFields($class)
    {
        $metadata = $this->reader->getMetadataForClass($class);
        $classMetadata = $metadata->classMetadata[$class];

        return $classMetadata->fields;
    }

    /**
     * Attempts to read the mapping of a specified property.
     *
     * @param string $class The class name to test (FQCN).
     * @param string $field The field
     *
     * @return null|array The field mapping.
     */
    public function getUploadableField($class, $field)
    {
        $fieldsMetadata = $this->getUploadableFields($class);

        return isset($fieldsMetadata[$field]) ? $fieldsMetadata[$field] : null;
    }
}
