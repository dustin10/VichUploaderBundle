<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Embeddable]
#[Vich\Uploadable]
class DummyNestedEmbeddable extends DummyEmbeddableParent
{
    #[Vich\UploadableField('dummy_image', fileNameProperty: 'imageName')]
    public ?object $image = null;

    #[ORM\Column(nullable: true)]
    public ?string $imageName = null;
}
