<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\AdvancedMetadataFactoryInterface;

/**
 * MetadataReader.
 *
 * Exposes a simple interface to read objects metadata.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class MetadataReader
{
    /**
     * @var AdvancedMetadataFactoryInterface $reader
     */
    protected $reader;

    /**
     * Constructs a new instance of the MetadataReader.
     *
     * @param AdvancedMetadataFactoryInterface $reader The "low-level" metadata reader.
     */
    public function __construct(AdvancedMetadataFactoryInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Tells if the given class is uploadable.
     *
     * @param string $class   The class name to test (FQCN).
     * @param string $mapping If given, also checks that the object has the given mapping.
     *
     * @return bool
     */
    public function isUploadable($class, $mapping = null)
    {
        $metadata = $this->reader->getMetadataForClass($class);

        if ($metadata === null) {
            return false;
        } elseif ($mapping === null) {
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
     * @return array A list of uploadable class names.
     */
    public function getUploadableClasses()
    {
        return $this->reader->getAllClassNames();
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
        $uploadableFields = array();

        foreach ($metadata->classMetadata as $classMetadata) {
            $uploadableFields = array_merge($uploadableFields, $classMetadata->fields);
        }

        return $uploadableFields;
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
