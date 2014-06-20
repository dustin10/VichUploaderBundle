<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\MetadataFactoryInterface;

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
     * @var MetadataFactoryInterface $reader
     */
    protected $reader;

    /**
     * Constructs a new instance of the MetadataReader.
     *
     * @param MetadataFactoryInterface $reader The "low-level" metadata reader.
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
     * Tells if the given class doing uploading right now
     *
     * @param string $object Object to test
     * @param string $class The class name of object to test (FQCN).
     *
     * @return bool
     */
    public function isUploading($object,$class)
    {
        $metadata = $this->reader->getMetadataForClass($class);
        if($metadata !== null) {
            /** @var $classMetadata \Vich\UploaderBundle\Metadata\ClassMetadata */
            foreach($metadata->classMetadata as $classMetadata) {
                foreach($classMetadata->fields as $fieldName => $fieldInfo) {
                    if(call_user_func(array($object,"get".ucfirst($fieldName))) !== null) {
                        return true;
                    }
                }
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
