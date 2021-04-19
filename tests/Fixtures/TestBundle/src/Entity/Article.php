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
     *
     * @var object|null
     */
    protected $attachment;

    /** @var string */
    protected $attachmentName;

    /**
     * @Vich\UploadableField(mapping="dummy_image", fileNameProperty="imageName", originalName="originalNameField", mimeType="mimeTypeField", size="sizeField")
     *
     * @var object|null
     */
    protected $image;

    /** @var string|null */
    protected $imageName;

    /** @var string|null */
    protected $originalNameField;

    /** @var string|null */
    protected $mimeTypeField;

    /** @var string|null */
    protected $sizeField;

    public function getAttachment(): void
    {
    }

    public function setAttachment(?object $attachment): void
    {
        $this->attachment = $attachment;
    }

    public function getAttachmentName(): ?string
    {
        return $this->attachmentName;
    }

    public function setAttachmentName(?string $attachmentName): void
    {
        $this->attachmentName = $attachmentName;
    }

    public function getImage(): void
    {
    }

    public function setImage(?object $image): void
    {
        $this->image = $image;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getOriginalNameField(): ?string
    {
        return $this->originalNameField;
    }

    public function setOriginalNameField(?string $originalNameField): void
    {
        $this->originalNameField = $originalNameField;
    }

    public function getMimeTypeField(): ?string
    {
        return $this->mimeTypeField;
    }

    public function setMimeTypeField(?string $mimeTypeField): void
    {
        $this->mimeTypeField = $mimeTypeField;
    }

    public function getSizeField(): ?string
    {
        return $this->sizeField;
    }

    public function setSizeField(?string $sizeField): void
    {
        $this->sizeField = $sizeField;
    }
}
