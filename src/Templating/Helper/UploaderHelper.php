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
     * @param object      $obj       The object
     * @param string|null $fieldName The field name
     *
     * @return string|null The public asset path or null if file not stored
     */
    public function asset(object $obj, ?string $fieldName = null): ?string
    {
        return $this->storage->resolveUri($obj, $fieldName);
    }
}
