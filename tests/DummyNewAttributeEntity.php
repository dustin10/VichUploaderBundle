<?php

namespace Vich\UploaderBundle\Tests;

use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Test entity using the new Attribute namespace to verify it works alongside the deprecated Annotation namespace.
 */
#[Vich\Uploadable]
class DummyNewAttributeEntity
{
    #[Vich\UploadableField('dummy_file', fileNameProperty: 'fileName')]
    protected ?object $file = null;

    protected ?string $fileName = null;

    protected ?int $size = null;

    public ?string $someProperty = null;

    public function getFile(): ?object
    {
        return $this->file;
    }

    public function setFile(?object $file): void
    {
        $this->file = $file;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function generateFileName(): string
    {
        return 'generated-file-name';
    }
}
