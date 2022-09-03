<?php

namespace Vich\TestBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;

class Product
{
    private ?\Symfony\Component\HttpFoundation\File\File $image = null;

    private ?string $imageName = null;

    private ?string $imageSize = null;

    private ?string $imageMimeType = null;

    private ?string $imageOriginalName = null;

    private ?string $title = null;

    public function getImage(): File
    {
        return $this->image;
    }

    public function setImage(File $image): void
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

    public function getImageSize(): string
    {
        return $this->imageSize;
    }

    public function setImageSize(string $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageMimeType(): ?string
    {
        return $this->imageMimeType;
    }

    public function setImageMimeType(string $imageMimeType): void
    {
        $this->imageMimeType = $imageMimeType;
    }

    public function getImageOriginalName(): string
    {
        return $this->imageOriginalName;
    }

    public function setImageOriginalName(string $imageOriginalName): void
    {
        $this->imageOriginalName = $imageOriginalName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
