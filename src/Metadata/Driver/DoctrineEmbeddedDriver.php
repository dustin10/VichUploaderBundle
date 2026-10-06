<?php

namespace Vich\UploaderBundle\Metadata\Driver;

use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Metadata\ClassMetadata as JMSClassMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @internal
 */
final readonly class DoctrineEmbeddedDriver implements AdvancedDriverInterface
{
    /**
     * @param ManagerRegistry[] $managerRegistryList
     */
    public function __construct(
        private AdvancedDriverInterface $driver,
        private array $managerRegistryList,
    ) {
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?JMSClassMetadata
    {
        $ormMetadata = $this->getOrmMetadata($class->name);
        if ($ormMetadata?->isEmbeddedClass) {
            return null;
        }

        $metadata = $this->driver->loadMetadataForClass($class);
        if (null === $ormMetadata || (null !== $metadata && !$metadata instanceof ClassMetadata)) {
            return $metadata;
        }

        foreach ($ormMetadata->embeddedClasses as $path => $embedded) {
            $embeddedMetadata = $this->loadEmbeddableMetadata(new \ReflectionClass(\is_array($embedded) ? $embedded['class'] : $embedded->class));
            if (null === $embeddedMetadata || [] === $embeddedMetadata->fields) {
                continue;
            }

            if (null === $metadata) {
                $metadata = new ClassMetadata($class->name);
                $metadata->fileResources[] = $class->getFileName();
            }

            \array_push($metadata->fileResources, ...$embeddedMetadata->fileResources);

            foreach ($embeddedMetadata->fields as $field) {
                foreach ($field as $key => $value) {
                    if ('mapping' !== $key && \is_string($value) && '' !== $value) {
                        $field[$key] = $path.'.'.$value;
                    }
                }

                $metadata->fields[$field['propertyName']] = $field;
            }
        }

        return $metadata;
    }

    public function getAllClassNames(): array
    {
        $classes = \array_filter(
            $this->driver->getAllClassNames(),
            fn (string $class): bool => !$this->getOrmMetadata($class)?->isEmbeddedClass,
        );

        foreach ($this->managerRegistryList as $managerRegistry) {
            foreach ($managerRegistry->getManagers() as $manager) {
                foreach ($manager->getMetadataFactory()->getAllMetadata() as $ormMetadata) {
                    if (!$ormMetadata instanceof OrmClassMetadata || $ormMetadata->isEmbeddedClass || [] === $ormMetadata->embeddedClasses) {
                        continue;
                    }

                    if (null !== $this->loadMetadataForClass($ormMetadata->getReflectionClass())) {
                        $classes[] = $ormMetadata->getName();
                    }
                }
            }
        }

        return \array_values(\array_unique($classes));
    }

    private function loadEmbeddableMetadata(\ReflectionClass $class): ?ClassMetadata
    {
        $hierarchy = [];
        do {
            $hierarchy[] = $class;
        } while (false !== $class = $class->getParentClass());

        $merged = null;
        foreach (\array_reverse($hierarchy) as $hierarchyClass) {
            $metadata = $this->driver->loadMetadataForClass($hierarchyClass);
            if (!$metadata instanceof ClassMetadata) {
                continue;
            }

            if (null === $merged) {
                $merged = $metadata;
                continue;
            }

            $merged->fileResources = [...$merged->fileResources, ...$metadata->fileResources];
            $merged->fields = [...$merged->fields, ...$metadata->fields];
        }

        return $merged;
    }

    /**
     * @param class-string $class
     */
    private function getOrmMetadata(string $class): ?OrmClassMetadata
    {
        foreach ($this->managerRegistryList as $managerRegistry) {
            $metadata = $managerRegistry->getManagerForClass($class)?->getClassMetadata($class);
            if ($metadata instanceof OrmClassMetadata) {
                return $metadata;
            }
        }

        return null;
    }
}
