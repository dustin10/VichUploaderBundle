<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class CallbackNamer implements NamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function name($object, PropertyMapping $mapping): string
    {
        if (!$object instanceof CallableNameProviderInterface) {
            throw new NameGenerationException(
                sprintf(
                    'Object "%s" must implement the "%s" interface to use the namer "%s".',
                    get_class($object),
                    CallableNameProviderInterface::class,
                    CallbackNamer::class
                )
            );
        }

        return $object->getUploadedFileName();
    }
}
