<?php

namespace Vich\UploaderBundle\EventListener\Propel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Vich\UploaderBundle\Adapter\AdapterInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * BaseListener
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class BaseListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $mapping;

    /**
     * @var AdapterInterface $adapter
     */
    protected $adapter;

    /**
     * @var MetadataReader $metadata
     */
    protected $metadata;

    /**
     * @var UploaderHandler $handler
     */
    protected $handler;

    /**
     * Constructs a new instance of BaseListener.
     *
     * @param string           $mapping  The mapping name.
     * @param AdapterInterface $adapter  The adapter.
     * @param MetadataReader   $metadata The metadata reader.
     * @param UploaderHandler  $handler  The upload handler.
     */
    public function __construct($mapping, AdapterInterface $adapter, MetadataReader $metadata, UploadHandler $handler)
    {
        $this->mapping = $mapping;
        $this->adapter = $adapter;
        $this->metadata = $metadata;
        $this->handler = $handler;
    }

    /**
     * Returns a list of uploadable fields for the given object and mapping.
     *
     * @param mixed $object The object to use.
     *
     * @return array<string> A list of field names.
     */
    protected function getUploadableFields($object)
    {
        $fields = $this->metadata->getUploadableFields(ClassUtils::getClass($object), $this->mapping);

        return array_map(function($data) {
            return $data['propertyName'];
        }, $fields);
    }
}
