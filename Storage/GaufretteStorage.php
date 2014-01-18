<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Exception\FileNotFound;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Knp\Bundle\GaufretteBundle\FilesystemMap;

use Gaufrette\Stream\Local as LocalStream;
use Gaufrette\StreamMode;
use Gaufrette\Adapter\MetadataSupporter;

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
     * @var string
     */
    protected $protocol;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param \Vich\UploaderBundle\Mapping\PropertyMappingFactory $factory       The factory.
     * @param \Knp\Bundle\GaufretteBundle\FilesystemMap           $filesystemMap Gaufrete filesystem factory.
     * @param string                                              $protocol      Gaufrette stream wrapper protocol.
     */
    public function __construct(PropertyMappingFactory $factory, FilesystemMap $filesystemMap, $protocol = 'gaufrette')
    {
        parent::__construct($factory);

        $this->filesystemMap = $filesystemMap;
        $this->protocol      = $protocol;
    }

    /**
     * Get filesystem adapter by key
     *
     * @param string $key
     *
     * @return \Gaufrette\Filesystem
     */
    protected function getFilesystem($key)
    {
        return $this->filesystemMap->get($key);
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpload(UploadedFile $file, $dir, $name)
    {
        $filesystem = $this->getFilesystem($dir);

        if ($filesystem->getAdapter() instanceof MetadataSupporter) {
            $filesystem->getAdapter()->setMetadata($name, array('contentType' => $file->getMimeType()));
        }

        $src = new LocalStream($file->getPathname());
        $dst = $filesystem->createStream($name);

        $src->open(new StreamMode('rb+'));
        $dst->open(new StreamMode('wb+'));

        while (!$src->eof()) {
            $data = $src->read(100000);
            $dst->write($data);
        }

        $dst->close();
        $src->close();
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove($dir, $name)
    {
        $adapter = $this->getFilesystem($dir);

        try {
            return $adapter->delete($name);
        } catch (FileNotFound $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath($dir, $name)
    {
        return $this->protocol.'://' . $dir . '/' . $name;
    }
}
