<?php

namespace Vich\UploaderBundle\Tests;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class TwoFieldsDummyEntity
{
    /**
     * @Vich\UploadableField(mapping="dummy_file", fileNameProperty="fileName")
     */
    protected $file;

    protected $fileName;

    /**
     * @Vich\UploadableField(mapping="dummy_image", fileNameProperty="imageName", originalName="originalNameField", mimeType="mimeTypeField", size="sizeField")
     */
    protected $image;

    protected $imageName;

    protected $originalNameField;

    protected $mimeTypeField;

    protected $sizeField;

    public function getFile()
    {
        $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getImage()
    {
        $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImageName()
    {
        return $this->imageName;
    }

    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    public function getOriginalNameField()
    {
        return $this->originalNameField;
    }

    public function setOriginalNameField($originalNameField)
    {
        $this->originalNameField = $originalNameField;
    }

    public function getMimeTypeField()
    {
        return $this->mimeTypeField;
    }

    public function setMimeTypeField($mimeTypeField)
    {
        $this->mimeTypeField = $mimeTypeField;
    }

    public function getSizeField()
    {
        return $this->sizeField;
    }

    public function setSizeField($sizeField)
    {
        $this->sizeField = $sizeField;
    }
}
