<?php

namespace Vich\TestBundle\Entity;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Article
{
    /**
     * @Vich\UploadableField(mapping="dummy_file", fileNameProperty="fileName")
     */
    protected $attachment;

    protected $attachmentName;

    /**
     * @Vich\UploadableField(mapping="dummy_image", fileNameProperty="imageName", originalName="originalNameField", mimeType="mimeTypeField", size="sizeField")
     */
    protected $image;

    protected $imageName;

    protected $originalNameField;

    protected $mimeTypeField;

    protected $sizeField;

    public function getAttachment(): void
    {
        $this->attachment;
    }

    public function setAttachment($attachment): void
    {
        $this->attachment = $attachment;
    }

    public function getAttachmentName()
    {
        return $this->attachmentName;
    }

    public function setAttachmentName($attachmentName): void
    {
        $this->attachmentName = $attachmentName;
    }

    public function getImage(): void
    {
        $this->image;
    }

    public function setImage($image): void
    {
        $this->image = $image;
    }

    public function getImageName()
    {
        return $this->imageName;
    }

    public function setImageName($imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getOriginalNameField()
    {
        return $this->originalNameField;
    }

    public function setOriginalNameField($originalNameField): void
    {
        $this->originalNameField = $originalNameField;
    }

    public function getMimeTypeField()
    {
        return $this->mimeTypeField;
    }

    public function setMimeTypeField($mimeTypeField): void
    {
        $this->mimeTypeField = $mimeTypeField;
    }

    public function getSizeField()
    {
        return $this->sizeField;
    }

    public function setSizeField($sizeField): void
    {
        $this->sizeField = $sizeField;
    }
}
