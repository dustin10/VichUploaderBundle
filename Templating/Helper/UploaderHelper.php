<?php

namespace Vich\UploaderBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Vich\UploaderBundle\Exception\MissingMappingException;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * UploaderHelper.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderHelper extends Helper
{
    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface $storage
     */
    protected $storage;

    /**
     * @var PropertyMappingFactory
     */
    protected $mappingFactory;

    /**
     * Constructs a new instance of UploaderHelper.
     *
     * @param \Vich\UploaderBundle\Storage\StorageInterface $storage The storage.
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $mappingFactory
     */
    public function __construct(StorageInterface $storage, PropertyMappingFactory $mappingFactory)
    {
        $this->storage = $storage;
        $this->mappingFactory = $mappingFactory;
    }

    /**
     * Gets the helper name.
     *
     * @return string The name
     */
    public function getName()
    {
        return 'vich_uploader';
    }

    /**
     * Gets the public path for the file associated with the
     * object.
     *
     * @param object $obj         The object.
     * @param string $fileIdentifier will try to resolve this as a mapping name or as a field name the first fail
     * @param string $className   The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return string The public asset path.
     */
    public function asset($obj, $fileIdentifier, $className = null)
    {
        try {
            return $this->storage->resolveUri($obj, $fileIdentifier, $className);
        } catch (MissingMappingException $e) {
            $mapping = $this->mappingFactory->fromField($obj, $fileIdentifier, $className);
            return $this->storage->resolveUri($obj, $mapping->getMappingName(), $className);
        }
    }
}
