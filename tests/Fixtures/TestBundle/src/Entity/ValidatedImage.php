<?php

namespace Vich\TestBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Vich\UploaderBundle\Validator\Constraints as VichAssert;

#[Vich\Uploadable]
class ValidatedImage
{
    protected ?int $id = null;

    protected ?string $title = null;

    protected ?\DateTime $updatedAt = null;

    #[Vich\UploadableField(
        mapping: 'image_mapping',
        fileNameProperty: 'imageEntity.name',
        size: 'imageEntity.size',
        mimeType: 'imageEntity.mimeType',
        originalName: 'imageEntity.originalName'
    )]
    #[VichAssert\FileRequired(target: 'imageEntity')]
    protected ?File $imageFile = null;

    protected ?EmbeddedFile $imageEntity = null;

    public function __construct()
    {
        $this->imageEntity = new EmbeddedFile();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageEntity(?EmbeddedFile $imageEntity): void
    {
        $this->imageEntity = $imageEntity;
    }

    public function getImageEntity(): ?EmbeddedFile
    {
        return $this->imageEntity;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
