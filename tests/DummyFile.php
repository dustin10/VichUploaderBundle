<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
class DummyFile
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column]
    protected ?string $title = null;

    #[ORM\Column(nullable: true)]
    protected ?\DateTime $updatedAt = null;

    #[Vich\UploadableField(mapping: 'image_mapping', fileNameProperty: 'imageName')]
    protected ?SymfonyFile $file = null;

    #[ORM\Column(nullable: true)]
    protected ?string $fileName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFile(): SymfonyFile
    {
        return $this->file;
    }

    public function setFile(SymfonyFile $file): void
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
}
