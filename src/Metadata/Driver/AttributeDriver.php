<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Metadata\ClassMetadata as JMSClassMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable as UploadableAnnotation;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField as UploadableFieldAnnotation;
use Vich\UploaderBundle\Mapping\Attribute\Uploadable;
use Vich\UploaderBundle\Mapping\Attribute\UploadableField;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class AttributeDriver implements AdvancedDriverInterface
{
    /**
     * @param \Doctrine\Persistence\ManagerRegistry[] $managerRegistryList
     */
    public function __construct(
        protected readonly AttributeReader $reader,
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
            // Support both new Attribute\ and deprecated Annotation\ namespaces
            $uploadableField = $this->reader->getPropertyAttribute($property, UploadableField::class);
            if (null === $uploadableField) {
                // Fallback to deprecated Annotation namespace
                $uploadableField = $this->reader->getPropertyAttribute($property, UploadableFieldAnnotation::class);
            }
            if (!$uploadableField instanceof UploadableField && !$uploadableField instanceof UploadableFieldAnnotation) {
                continue;
            }
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

        foreach ($this->managerRegistryList as $managerRegistry) {
            $managers = $managerRegistry->getManagers();
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
        // Support both new Attribute\ and deprecated Annotation\ namespaces
        $uploadable = $this->reader->getClassAttribute($class, Uploadable::class);
        if (null === $uploadable) {
            // Fallback to deprecated Annotation namespace
            $uploadable = $this->reader->getClassAttribute($class, UploadableAnnotation::class);
        }

        return null !== $uploadable;
    }
}
