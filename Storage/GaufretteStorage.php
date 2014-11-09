<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Exception\FileNotFound;
use Vich\UploaderBundle\Mapping\PropertyMapping;
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
     * {@inheritDoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $dir, $name)
    {
        $filesystem = $this->getFilesystem($mapping);

        if ($filesystem->getAdapter() instanceof MetadataSupporter) {
            $filesystem->getAdapter()->setMetadata($dir . $name, array('contentType' => $file->getMimeType()));
        }

        $src = new LocalStream($file->getPathname());
        $dst = $filesystem->createStream($dir . $name);

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
    protected function doRemove(PropertyMapping $mapping, $dir, $name)
    {
        $filesystem = $this->getFilesystem($mapping);

        try {
            return $filesystem->delete($dir . $name);
        } catch (FileNotFound $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name)
    {
        $fsIdentifier = $mapping->getUploadDestination();

        return $this->protocol.'://' . $fsIdentifier . '/' . $dir . $name;
    }

    /**
     * Get filesystem adapter from the property mapping
     *
     * @param PropertyMapping $mapping
     *
     * @return \Gaufrette\Filesystem
     */
    protected function getFilesystem(PropertyMapping $mapping)
    {
        return $this->filesystemMap->get($mapping->getUploadDestination());
    }
}
