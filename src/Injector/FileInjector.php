<?php

namespace Vich\UploaderBundle\Injector;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * FileInjector.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class FileInjector implements FileInjectorInterface
{
    public function __construct(private readonly StorageInterface $storage)
    {
    }

    public function injectFile(object $obj, PropertyMapping $mapping): void
    {
        $path = $this->storage->resolvePath($obj, $mapping->getFilePropertyName());

        if (null !== $path) {
            $mapping->setFile($obj, new File($path, false));
        }
    }
}
