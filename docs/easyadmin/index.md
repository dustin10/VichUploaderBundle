# Interconnect with EasyAdmin

Considering your image file field named `imageFile` in your entity for image and `documentFile` for docs.

Of course, you can adapt example with your namings.

In the entity you've got :

```php
#[ORM\Column(length: 255, nullable: true)]
private ?string $image = null;

// NOTE: This is not a mapped field of entity metadata, just a simple property.
#[Vich\UploadableField(mapping: 'products', fileNameProperty: 'image')]
private ?File $imageFile = null;

#[ORM\Column(length: 255, nullable: true)]
private ?string $document = null;

// NOTE: This is not a mapped field of entity metadata, just a simple property.
#[Vich\UploadableField(mapping: 'products', fileNameProperty: 'document')]
private ?File $documentFile = null;
```

In the EasyAdmin Crud controller set :

```php
<?php

namespace App\Controller\Admin;

use Vich\UploaderBundle\EasyAdmin\Field\VichImageField;
use Vich\UploaderBundle\EasyAdmin\Field\VichFileField;
/// ....

class SampleCrudController extends AbstractCrudController
{
    ////....

    public function configureFields(string $pageName): iterable
    {
        //// Other fields

        yield VichImageField::new('imageFile'); // For image type
        yield VichFileField::new('documentFile'); // For file type

        ////
    }
}
```

If you use array method :

```php
<?php

namespace App\Controller\Admin;

use Vich\UploaderBundle\EasyAdmin\Field\VichImageField;
use Vich\UploaderBundle\EasyAdmin\Field\VichFileField;
/// ....

class SampleCrudController extends AbstractCrudController
{
    ////....

    public function configureFields(string $pageName): iterable
    {
        return [
            //// Other fields
            VichImageField::new('imageFile'), // For image type
            VichFileField::new('documentFile'), // For file type
        ];
    }
}
```

## LiipImagineBundle

To make your thumbnails for list views friendly use LiipImagineBundle and add the `admin_thumbnail` filter

Add in your crud field

```php
VichImageField::new('imageFile')            
    ->setFormTypeOption('imagine_pattern', 'admin_thumbnail');
```

Sample of config

```yaml
# Documentation on how to configure the bundle can be found at: https://symfony.com/doc/current/bundles/LiipImagineBundle/basic-usage.html
liip_imagine:
    # valid drivers options include "gd" or "gmagick" or "imagick"
    driver: "gd"

    webp:
        generate: true

    filter_sets:
        admin_thumbnail:
            filters:
                thumbnail:
                    size: [150, 150]
```

## That was it

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
