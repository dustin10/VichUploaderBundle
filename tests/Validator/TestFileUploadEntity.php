<?php

namespace Vich\UploaderBundle\Tests\Validator;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Vich\UploaderBundle\Validator\Constraints as VichAssert;

/**
 * Test entity for VichFileRequired validator tests.
 */
#[Vich\Uploadable]
class TestFileUploadEntity
{
    #[Vich\UploadableField(
        mapping: 'test_images',
        fileNameProperty: 'image.name',
        size: 'image.size',
        mimeType: 'image.mimeType',
        originalName: 'image.originalName',
        dimensions: 'image.dimensions'
    )]
    #[VichAssert\FileRequired(target: 'image')]
    public File|UploadedFile|null $imageFile = null;

    public ?EmbeddedFile $image = null;

    #[Vich\UploadableField(
        mapping: 'test_documents',
        fileNameProperty: 'document.name',
        size: 'document.size',
        mimeType: 'document.mimeType',
        originalName: 'document.originalName'
    )]
    #[VichAssert\FileRequired(target: 'document')]
    public File|UploadedFile|null $documentFile = null;

    public ?EmbeddedFile $document = null;

    // File field without VichFileRequired constraint for negative testing
    #[Vich\UploadableField(
        mapping: 'test_optional',
        fileNameProperty: 'optionalFileEntity.name'
    )]
    public File|UploadedFile|null $optionalFile = null;

    public ?EmbeddedFile $optionalFileEntity = null;

    // Non-file property for testing invalid targets
    public ?string $title = null;

    public function __construct()
    {
        $this->image = new EmbeddedFile();
        $this->document = new EmbeddedFile();
        $this->optionalFileEntity = new EmbeddedFile();
    }

    public function getImageFile(): File|UploadedFile|null
    {
        return $this->imageFile;
    }

    public function setImageFile(File|UploadedFile|null $imageFile): void
    {
        $this->imageFile = $imageFile;
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }

    public function setImage(?EmbeddedFile $image): void
    {
        $this->image = $image;
    }

    public function getDocumentFile(): File|UploadedFile|null
    {
        return $this->documentFile;
    }

    public function setDocumentFile(File|UploadedFile|null $documentFile): void
    {
        $this->documentFile = $documentFile;
    }

    public function getDocument(): ?EmbeddedFile
    {
        return $this->document;
    }

    public function setDocument(?EmbeddedFile $document): void
    {
        $this->document = $document;
    }

    public function getOptionalFile(): File|UploadedFile|null
    {
        return $this->optionalFile;
    }

    public function setOptionalFile(File|UploadedFile|null $optionalFile): void
    {
        $this->optionalFile = $optionalFile;
    }

    public function getOptionalFileEntity(): ?EmbeddedFile
    {
        return $this->optionalFileEntity;
    }

    public function setOptionalFileEntity(?EmbeddedFile $optionalFileEntity): void
    {
        $this->optionalFileEntity = $optionalFileEntity;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
