<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Metadata\Driver\DriverInterface;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @deprecated
     */
    const UPLOADABLE_ANNOTATION = Uploadable::class;

    /**
     * @deprecated
     */
    const UPLOADABLE_FIELD_ANNOTATION = UploadableField::class;

    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if (!$this->isUploadable($class)) {
            return;
        }

        $metadata = new ClassMetadata($class->name);

        foreach ($class->getProperties() as $property) {
            $uploadableField = $this->reader->getPropertyAnnotation($property, UploadableField::class);
            if ($uploadableField === null) {
                continue;
            }
            /* @var $uploadableField UploadableField */

            $fieldMetadata = [
                'mapping' => $uploadableField->getMapping(),
                'propertyName' => $property->getName(),
                'fileNameProperty' => $uploadableField->getFileNameProperty(),
                'size' => $uploadableField->getSize(),
                'mimeType' => $uploadableField->getMimeType(),
                'originalName' => $uploadableField->getOriginalName(),
            ];
            //TODO: store UploadableField object instead of array
            $metadata->fields[$property->getName()] = $fieldMetadata;
        }

        return $metadata;
    }

    protected function isUploadable(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, Uploadable::class) !== null;
    }
}
