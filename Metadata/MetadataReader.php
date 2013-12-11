<?php

namespace Vich\UploaderBundle\Metadata;

use Metadata\MetadataFactoryInterface;

/**
 * MetadataReader
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
     * @param Reflectionclass $class The class to test.
     *
     * @return bool
     */
    public function isUploadable(\ReflectionClass $class)
    {
        $metadata = $this->reader->getMetadataForClass($class->name);

        return $metadata !== null;
    }

    /**
     * Returns a list of uploadable classes.
     *
     * @return array A list of class names.
     */
    public function getUploadableClasses()
    {
        return $this->reader->getAllClassNames();
    }

    /**
     * Attempts to read the uploadable fields.
     *
     * @param \ReflectionClass $class The reflection class.
     *
     * @return array A list of uploadable fields.
     */
    public function getUploadableFields(\ReflectionClass $class)
    {
        $metadata = $this->reader->getMetadataForClass($class->getName());
        $classMetadata = $metadata->classMetadata[$class->getName()];

        return $classMetadata->fields;
    }

    /**
     * Attempts to read the mapping of a specified property.
     *
     * @param \ReflectionClass $class The class.
     * @param string           $field The field
     *
     * @return null|array The field mapping.
     */
    public function getUploadableField(\ReflectionClass $class, $field)
    {
        $fieldsMetadata = $this->getUploadableFields($class);

        return isset($fieldsMetadata[$field]) ? $fieldsMetadata[$field] : null;
    }
}
