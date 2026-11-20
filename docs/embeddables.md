# Uploadable fields in Doctrine embeddables

Uploadable fields can live inside a Doctrine ORM embeddable, including embeddables nested in
other embeddables. The bundle registers them on the entity under their dotted property path,
such as `images.banners.largeRectangle`.

## Mapping

The embeddable is mapped like any uploadable class, with property names relative to the embeddable
itself. The entity needs no mapping of its own for these fields: it gets them from the embeddables
listed in its Doctrine metadata.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Promotion
{
    // ...

    #[ORM\Embedded(columnPrefix: 'images_')]
    private Images $images;

    public function __construct()
    {
        $this->images = new Images();
    }

    public function getImages(): Images
    {
        return $this->images;
    }
}
```

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Embeddable]
#[Vich\Uploadable]
class Images
{
    #[Vich\UploadableField(mapping: 'thumbnails', fileNameProperty: 'thumbnailName', size: 'thumbnailSize')]
    private ?File $thumbnail = null;

    #[ORM\Column(nullable: true)]
    private ?string $thumbnailName = null;

    #[ORM\Column(nullable: true)]
    private ?int $thumbnailSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function setThumbnail(?File $thumbnail = null): void
    {
        $this->thumbnail = $thumbnail;

        if (null !== $thumbnail) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    // getters and setters for the other properties
}
```

The embeddable can also be mapped in [YAML](mapping/yaml.md) or [XML](mapping/xml.md), and the
entity and its embeddables do not need to use the same format. Fields declared on parent classes of an
embeddable are included.

Doctrine only flushes an entity when a mapped column changes, and the columns of its embeddables count
as its own. Change a mapped column on the entity or any of its embeddables whenever a file is set, or
the upload is not persisted. A column in the embeddable itself, like `$updatedAt` above, is the one its
setter can reach.

The `Vich\UploaderBundle\Entity\File` embeddable works inside an embeddable too:
`fileNameProperty: 'thumbnailFile.name'` resolves against the embeddable that declares the field.

## Using the field

Refer to the field on the entity by its dotted path. An embeddable is not uploadable on its own, so
always pass the entity:

```twig
<img src="{{ vich_uploader_asset(promotion, 'images.thumbnail') }}" alt="">
```

`VichFileType` and `VichImageType` need no extra options. Nest the embeddable's form type in the
entity's form type, and the field resolves its URI, download link and delete checkbox against the
entity:

```php
$builder->add('images', ImagesType::class);
```

```php
$builder->add('thumbnail', VichImageType::class, ['required' => false]);
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](index.md)
