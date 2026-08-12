<?php

namespace Vich\TestBundle\Entity;

use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Entity whose file name is a non-nullable property, like the media object described in #1117.
 */
#[Vich\Uploadable]
class NotNullableArticle
{
    #[Vich\UploadableField(mapping: 'dummy_image', fileNameProperty: 'imageName', size: 'sizeField')]
    protected ?object $image = null;

    protected string $imageName = '';

    protected ?string $sizeField = null;

    // Non-nullable, written directly by the property accessor (no setter).
    public int $publicSize = 0;

    public function getImage(): ?object
    {
        return $this->image;
    }

    public function setImage(?object $image): void
    {
        $this->image = $image;
    }

    public function getImageName(): string
    {
        return $this->imageName;
    }

    public function setImageName(string $imageName): void
    {
        $this->imageName = $imageName;
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
