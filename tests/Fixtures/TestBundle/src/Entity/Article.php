<?php

namespace Vich\TestBundle\Entity;

use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
#[Vich\Uploadable]
class Article
{
    #[Vich\UploadableField(mapping: 'dummy_file', fileNameProperty: 'fileName')]
    protected ?object $attachment = null;

    protected ?string $attachmentName = null;

    #[Vich\UploadableField(mapping: 'dummy_image', fileNameProperty: 'imageName', size: 'sizeField', mimeType: 'mimeTypeField', originalName: 'originalNameField')]
    protected ?object $image = null;

    protected ?string $imageName = null;

    protected ?string $originalNameField = null;

    protected ?string $mimeTypeField = null;

    protected ?string $sizeField = null;

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
