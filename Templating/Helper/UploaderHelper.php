<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * UploaderHelper.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderHelper extends Helper
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
     * @param string       $fieldName The field name
     * @param string       $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return string|null The public asset path or null if file not stored
     */
    public function asset($obj, string $fieldName, ?string $className = null)
    {
        return $this->storage->resolveUri($obj, $fieldName, $className);
    }
}
