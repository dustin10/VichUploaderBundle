<?php

namespace Vich\UploaderBundle\Handler;

use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class AbstractHandler
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory
     */
    protected $factory;

    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

    /**
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory The mapping factory.
     * @param \Vich\UploaderBundle\Storage\StorageInterface       $storage The storage.
     */
    public function __construct(PropertyMappingFactory $factory, StorageInterface $storage)
    {
        $this->factory = $factory;
        $this->storage = $storage;
    }

    protected function getMapping($obj, $fieldName, $className = null)
    {
        $mapping = $this->factory->fromField($obj, $fieldName, $className);

        if ($mapping === null) {
            throw new MappingNotFoundException(sprintf('Mapping not found for field "%s"', $fieldName));
        }

        return $mapping;
    }
}
