<?php

namespace Vich\UploaderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class File
{
    /**
     * @ORM\Column(name="name", nullable=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="original_name", nullable=true)
     *
     * @var string
     */
    protected $originalName;

    /**
     * @ORM\Column(name="mime_type", nullable=true)
     *
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(name="size", type="integer", nullable=true)
     *
     * @var int
     */
    protected $size;

    /**
     * @ORM\Column(name="dimensions", type="simple_array", nullable=true)
     *
     * @var array<int, int>
     */
    protected $dimensions;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): void
    {
        $this->originalName = $originalName;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function setDimensions(?array $dimensions): void
    {
        $this->dimensions = $dimensions;
    }
}
