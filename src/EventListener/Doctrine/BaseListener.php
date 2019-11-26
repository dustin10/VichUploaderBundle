<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * BaseListener.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class BaseListener implements EventSubscriber
{
    /**
     * @var string
     */
    protected $mapping;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var MetadataReader
     */
    protected $metadata;

    /**
     * @var UploadHandler
     */
    protected $handler;

    public function __construct(string $mapping, AdapterInterface $adapter, MetadataReader $metadata, UploadHandler $handler)
    {
        $this->mapping = $mapping;
        $this->adapter = $adapter;
        $this->metadata = $metadata;
        $this->handler = $handler;
    }

    /**
     * Checks if the given object is uploadable using the current mapping.
     *
     * @param object $object The object to test
     */
    protected function isUploadable($object): bool
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
    protected function getUploadableFields($object): array
    {
        $fields = $this->metadata->getUploadableFields(ClassUtils::getClass($object), $this->mapping);

        return \array_map(function ($data) {
            return $data['propertyName'];
        }, $fields);
    }
}
