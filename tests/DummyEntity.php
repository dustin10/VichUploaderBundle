<?php

namespace Vich\UploaderBundle\Tests;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DummyEntity
{
    /**
     * @Vich\UploadableField(mapping="dummy_file", fileNameProperty="fileName")
     */
    protected $file;

    protected $fileName;

    protected $size;

    public $someProperty;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): void
    {
        $this->file = $file;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size): void
    {
        $this->size = $size;
    }

    public function generateFileName(): string
    {
        return 'generated-file-name';
    }
}
