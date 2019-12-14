<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * UploaderHelper.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderHelper
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getName(): string
    {
        return 'vich_uploader';
    }

    /**
     * Gets the public path for the file associated with the
     * object.
     *
     * @param object|array $obj       The object
     * @param string|null  $fieldName The field name
     * @param string|null  $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return string|null The public asset path or null if file not stored
     */
    public function asset($obj, ?string $fieldName = null, ?string $className = null)
    {
        return $this->storage->resolveUri($obj, $fieldName, $className);
    }
}
