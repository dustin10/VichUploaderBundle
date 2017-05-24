<?php

namespace Vich\TestBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;

class Product
{
    /**
     * @var File
     */
    private $image;

    private $imageName;

    private $imageSize;

    private $imageMimeType;

    private $imageOriginalName;

    private $title;

    /**
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param File $image
     */
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

    public function getImageSize()
    {
        return $this->imageSize;
    }

    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
    }

    public function getImageMimeType()
    {
        return $this->imageMimeType;
    }

    public function setImageMimeType($imageMimeType)
    {
        $this->imageMimeType = $imageMimeType;
    }

    public function getImageOriginalName()
    {
        return $this->imageOriginalName;
    }

    public function setImageOriginalName($imageOriginalName)
    {
        $this->imageOriginalName = $imageOriginalName;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
