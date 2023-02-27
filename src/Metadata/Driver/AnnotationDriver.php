<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Metadata\ClassMetadata as JMSClassMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class AnnotationDriver implements AdvancedDriverInterface
{
    /**
     * @param \Doctrine\Persistence\ManagerRegistry[] $managerRegistryList
     */
    public function __construct(
        protected readonly AnnotationReader|AttributeReader $reader,
        private readonly array $managerRegistryList
    ) {
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?JMSClassMetadata
    {
        if (!$this->isUploadable($class)) {
            return null;
        }

        $classMetadata = new ClassMetadata($class->name);
        $classMetadata->fileResources[] = $class->getFileName();

        $classes = [];
        do {
            $classes[] = $class;
            $class = $class->getParentClass();
        } while (false !== $class);
        $classes = \array_reverse($classes);
        $properties = [];
        foreach ($classes as $cls) {
            $properties = [...$properties, ...$cls->getProperties()];
        }

        foreach ($properties as $property) {
            $uploadableField = $this->reader->getPropertyAnnotation($property, UploadableField::class);
            if (null === $uploadableField) {
                continue;
            }
            /* @var $uploadableField UploadableField */
            // TODO: try automatically determinate target fields if embeddable used

            $fieldMetadata = [
                'mapping' => $uploadableField->getMapping(),
                'propertyName' => $property->getName(),
                'fileNameProperty' => $uploadableField->getFileNameProperty(),
                'size' => $uploadableField->getSize(),
                'mimeType' => $uploadableField->getMimeType(),
                'originalName' => $uploadableField->getOriginalName(),
                'dimensions' => $uploadableField->getDimensions(),
            ];

            // TODO: store UploadableField object instead of array
            $classMetadata->fields[$property->getName()] = $fieldMetadata;
        }

        return $classMetadata;
    }

    public function getAllClassNames(): array
    {
        $classes = [];
        $metadata = [];

        foreach ($this->managerRegistryList as $managerRegisty) {
            $managers = $managerRegisty->getManagers();
            foreach ($managers as $manager) {
                $metadata[] = $manager->getMetadataFactory()->getAllMetadata();
            }
        }

        $metadata = \array_merge(...$metadata);

        /** @var \Doctrine\Persistence\Mapping\ClassMetadata $classMeta */
        foreach ($metadata as $classMeta) {
            if ($this->isUploadable(new \ReflectionClass($classMeta->getName()))) {
                $classes[] = $classMeta->getName();
            }
        }

        return $classes;
    }

    protected function isUploadable(\ReflectionClass $class): bool
    {
        return null !== $this->reader->getClassAnnotation($class, Uploadable::class);
    }
}
