<?php

namespace Vich\UploaderBundle\Storage;

use Gaufrette\Exception\FileNotFound;
use Gaufrette\Stream\Local as LocalStream;
use Gaufrette\StreamMode;
use Gaufrette\Adapter\MetadataSupporter;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

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
     * @param PropertyMappingFactory $factory       The factory.
     * @param FilesystemMap          $filesystemMap Gaufrete filesystem factory.
     * @param string                 $protocol      Gaufrette stream wrapper protocol.
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
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, $destinationPath)
    {
        $fs = $this->getFilesystem($mapping->getUploadDestination());

        if ($fs->getAdapter() instanceof MetadataSupporter) {
            $fs->getAdapter()->setMetadata($name, array('contentType' => $file->getMimeType()));
        }

        // just to make sure that $dir is created before we start writing
        // @todo: find a better way to do this
        $fs->write($destinationPath, 'test');

        $src = new LocalStream($file->getPathname());
        $dst = $fs->createStream($destinationPath);

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
    protected function doRemove(PropertyMapping $mapping, $path)
    {
        $fs = $this->getFilesystem($mapping->getUploadDestination());

        try {
            return $fs->delete($path);
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
}
