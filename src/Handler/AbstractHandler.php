<?php

namespace Vich\UploaderBundle\Handler;

use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
abstract class AbstractHandler
{
    public function __construct(
        protected readonly PropertyMappingFactory $factory,
        protected readonly StorageInterface $storage,
    ) {
    }

    /**
     * @throws MappingNotFoundException
     */
    protected function getMapping(object|array $obj, string $fieldName, ?string $className = null): ?PropertyMapping
    {
        $mapping = $this->factory->fromField($obj, $fieldName, $className);

        if (null === $mapping) {
            throw new MappingNotFoundException(\sprintf('Mapping not found for field "%s"', $fieldName));
        }

        return $mapping;
    }
}
