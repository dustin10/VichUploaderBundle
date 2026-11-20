<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Embeddable]
#[Vich\Uploadable]
class DummyEmbeddable
{
    #[Vich\UploadableField('dummy_file', fileNameProperty: 'fileName', size: 'fileSize', mimeType: 'fileMimeType')]
    public ?object $file = null;

    #[ORM\Column(nullable: true)]
    public ?string $fileName = null;

    #[ORM\Column(nullable: true)]
    public ?int $fileSize = null;

    #[ORM\Column(nullable: true)]
    public ?string $fileMimeType = null;

    #[ORM\Embedded(class: DummyNestedEmbeddable::class)]
    public DummyNestedEmbeddable $nested;

    public function __construct()
    {
        $this->nested = new DummyNestedEmbeddable();
    }
}
