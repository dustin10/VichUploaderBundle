<?php

namespace Vich\UploaderBundle\Storage;

use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorage extends AbstractStorage
{
    private $fs;

    public function __construct(Filesystem $fs, PropertyMappingFactory $factory)
    {
        parent::__construct($factory);

        $this->fs = $fs;
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpload(UploadedFile $file, $dir, $name)
    {
        $stream = fopen($file->getRealPath(), 'r+');
        $this->fs->writeStream($dir.'/'.$name, $stream, array(
            'content-type' => $file->getMimeType()
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function doRemove($dir, $name)
    {
        $this->fs->delete($dir.'/'.$name);
    }

    /**
     * {@inheritDoc}
     */
    protected function doResolvePath($dir, $name)
    {
        return $dir.'/'.$name;
    }
} 