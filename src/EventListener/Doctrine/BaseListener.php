<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * BaseListener.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class BaseListener
{
    public function __construct(
        protected readonly string $mapping,
        protected readonly AdapterInterface $adapter,
        protected readonly MetadataReader $metadata,
        protected readonly UploadHandler $handler,
    ) {
    }

    /**
     * Checks if the given object is uploadable using the current mapping.
     *
     * @param object $object The object to test
     */
    protected function isUploadable(object $object): bool
    {
        return $this->metadata->isUploadable(ClassUtils::getClass($object), $this->mapping);
    }

    /**
     * Returns a list of uploadable fields for the given object and mapping.
     *
     * @param object $object The object to use
     *
     * @return array|string[] A list of field names
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    protected function getUploadableFields(object $object): array
    {
        $fields = $this->metadata->getUploadableFields(ClassUtils::getClass($object), $this->mapping);

        return \array_map(static fn (array $data): string => $data['propertyName'], $fields);
    }
}
