<?php
namespace Vich\UploaderBundle\Storage;

use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileSystemStorage.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory
     */
    protected $factory;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory The factory.
     */
    public function __construct(PropertyMappingFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Do real upload
     *
     * @param PropertyMapping $mapping         The mapping representing the current object.
     * @param UploadedFile    $file            The file being uploaded.
     * @param string          $destinationPath The destination path of the file.
     */
    abstract protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $destinationPath);

    /**
     * Do real remove
     *
     * @param PropertyMapping $mapping The mapping representing the current object.
     * @param string          $path    The path of the file to remove.
     *
     * @return boolean Whether the file has been removed or not.
     */
    abstract protected function doRemove(PropertyMapping $mapping, $path);

    /**
     * Do resolve path
     *
     * @param string $dir
     * @param string $name
     *
     * @return string
     */
    abstract protected function doResolvePath($dir, $path);

    /**
     * {@inheritDoc}
     */
    public function upload($object, PropertyMapping $mapping)
    {
        $file = $mapping->getFile($object);

        if ($file === null || !($file instanceof UploadedFile)) {
            return;
        }

        // keep the original name by default
        $name = $file->getClientOriginalName();

        // but use the namer if there is one
        if ($mapping->hasNamer()) {
            $name = $mapping->getNamer()->name($mapping, $object);
        }

        // update the filename
        $mapping->setFileName($object, $name);

        // determine the upload directory to use
        if ($mapping->hasDirectoryNamer()) {
            $dir = $mapping->getDirectoryNamer()->name($mapping, $object);
            $name = $dir . DIRECTORY_SEPARATOR . $name;

            // store the complete path in the filename
            // @note: we do this because the FileInjector needs the
            // directory, and the DirectoryNamer might need the File object
            // to compute it
            $mapping->setFileName($object, $name);
        }

        // and finalize the upload
        $this->doUpload($mapping, $file, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($object, PropertyMapping $mapping)
    {
        $name = $mapping->getFileName($object);

        if (null !== $name) {
            $this->doRemove($mapping, $name);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolvePath($obj, $field, $className = null)
    {
        list($mapping, $name) = $this->getFileName($obj, $field, $className);

        return $this->doResolvePath($mapping->getUploadDestination(), $name);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveUri($obj, $field, $className = null)
    {
        list($mapping, $filename) = $this->getFileName($obj, $field, $className);
        $uriPrefix = $mapping->getUriPrefix();

        return $filename ? ($uriPrefix . '/' . $filename) : '';
    }

    protected function getFileName($obj, $field, $className = null)
    {
        $mapping = $this->factory->fromField($obj, $field, $className);
        if (null === $mapping) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to find uploadable field named: "%s"', $field
            ));
        }

        $name = $mapping->getFileName($obj);
        if ($name === null) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to get filename property value: "%s"', $field
            ));
        }

        return array($mapping, $name);
    }
}
