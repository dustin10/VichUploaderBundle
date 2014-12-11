<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Metadata\Driver\DriverInterface;

use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * Annotation driver
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class AnnotationDriver implements DriverInterface
{
    const UPLOADABLE_ANNOTATION         = 'Vich\UploaderBundle\Mapping\Annotation\Uploadable';
    const UPLOADABLE_FIELD_ANNOTATION   = 'Vich\UploaderBundle\Mapping\Annotation\UploadableField';

    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if (!$this->isUploadable($class)) {
            return null;
        }

        $metadata = new ClassMetadata($class->name);

        foreach ($class->getProperties() as $property) {
            $uploadableField = $this->reader->getPropertyAnnotation($property, self::UPLOADABLE_FIELD_ANNOTATION);
            if ($uploadableField === null) {
                continue;
            }

            $fieldMetadata = array(
                'mapping'           => $uploadableField->getMapping(),
                'propertyName'      => $property->getName(),
                'fileNameProperty'  => $uploadableField->getFileNameProperty(),
            );

            $metadata->fields[$property->getName()] = $fieldMetadata;
        }

        return $metadata;
    }

    protected function isUploadable(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, self::UPLOADABLE_ANNOTATION) !== null;
    }
}
