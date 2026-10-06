<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DummyEmbeddingEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Embedded(class: DummyEmbeddable::class)]
    public DummyEmbeddable $meta;

    public function __construct()
    {
        $this->meta = new DummyEmbeddable();
    }
}
