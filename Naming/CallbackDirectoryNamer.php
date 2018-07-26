<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class CallbackDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        if (!$object instanceof CallableDirectoryNameProviderInterface) {
            throw new NameGenerationException(
                sprintf(
                    'Object "%s" must implement the "%s" interface to use the directory namer "%s".',
                    get_class($object),
                    CallableDirectoryNameProviderInterface::class,
                    CallbackDirectoryNamer::class
                )
            );
        }

        return $object->getUploadedDirectoryName();
    }
}
