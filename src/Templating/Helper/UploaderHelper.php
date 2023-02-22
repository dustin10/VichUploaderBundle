<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * UploaderHelper.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class UploaderHelper implements UploaderHelperInterface
{
    public function __construct(private readonly StorageInterface $storage)
    {
    }

    public function getName(): string
    {
        return 'vich_uploader';
    }

    /**
     * Gets the public path for the file associated with the
     * object.
     *
     * @param object|array $obj       The object or array
     * @param string|null  $fieldName The field name
     * @param string|null  $className The class name with the uploadable field. Mandatory if $obj is an array
     *
     * @return string|null The public asset path or null if file not stored
     */
    public function asset(object|array $obj, ?string $fieldName = null, ?string $className = null): ?string
    {
        return $this->storage->resolveUri($obj, $fieldName, $className);
    }
}
