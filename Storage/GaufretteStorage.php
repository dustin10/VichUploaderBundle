<?php

namespace Vich\UploaderBundle\Storage;

use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Mapping\PropertyMapping;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Knp\Bundle\GaufretteBundle\FilesystemMap;

use Gaufrette\FileStream\Local;
use Gaufrette\StreamMode;

/**
 * GaufretteStorage.
 *
 * @author Stefan Zerkalica <zerkalica@gmail.com>
 */
class GaufretteStorage extends AbstractStorage
{
    /**
     * @var FilesystemMap
     */
    protected $filesystemMap;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory       The factory.
     * @param FilesystemMap                                       $filesystemMap Gaufrete filesystem factory.
     */
    public function __construct(PropertyMappingFactory $factory, FilesystemMap $filesystemMap)
    {
        parent::__construct($factory);

        $this->filesystemMap = $filesystemMap;
    }

    /**
     * Get filesystem adapter by key
     *
     * @param string $key
     *
     * @return \Gaufrette\Filesystem
     */
    protected function getAdapter($key)
    {
        return $this->filesystemMap->get($key);
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpload(UploadedFile $file, $dir, $name)
    {
        $adapter = $this->getAdapter($dir);

        $src = new Local($file->getPathname());
        $dst = $adapter->createFileStream($name);

        $src->open(new StreamMode('rb+'));
        $dst->open(new StreamMode('ab+'));

        while (!$src->eof()) {
            $data    = $src->read(100000);
            $written = $dst->write($data);
        }
        $dst->close();
        $src->close();
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove($dir, $name)
    {
        $adapter = $this->getAdapter($dir);

        return $adapter->delete($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath($dir, $name)
    {
        return 'gaufrette://' . $dir . DIRECTORY_SEPARATOR . $name;
    }
}
