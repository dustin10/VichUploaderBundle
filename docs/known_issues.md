# Known issues

## The file is not updated if there are no other changes in the entity

As the bundle is listening to Doctrine `prePersist` and `preUpdate` events, which are not fired
when there is no change on field mapped by Doctrine, the file upload is not handled if the image field
is the only updated.

A workaround to solve this issue is to manually generate a change:

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Product
{
    // ...

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $updatedAt = null;

    // ...

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        // Only change the updated af if the file is really uploaded to avoid database updates.
        // This is needed when the file should be set when loading the entity.
        if ($this->imageFile instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }
    }
}
```

See issue [GH-123](https://github.com/dustin10/VichUploaderBundle/issues/123)

## Image not deleted with cascade deletion and Doctrine

Just check the following options: `cascade={"remove"}` and `orphanRemoval=true`.

```yaml
Vendor\Bundle\CoolBundle\Entity\CoolEntity:
    type: entity

    fields: ...

    oneToMany:
        images:
           targetEntity: CoolEntityImage
           mappedBy: bike
           cascade: [persist, merge, remove]
           orphanRemoval: true
```

## No Upload is triggered when manually injecting an instance of Symfony\Component\HttpFoundation\File\File

An upload will only be triggered if an instance of `Symfony\Component\HttpFoundation\File\UploadedFile`
is injected into the setter for the `Vich\UploadableField` property of your class. Normally this is done
automatically if using Symfony Form but if your application logic is attempting to do this manually, and you
inject an instance of `Symfony\Component\HttpFoundation\File\File` *instead* the bundle will silently ignore
your attempted upload.
Consider the following class:

``` php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class Product
{
    #[Vich\UploadableField(mapping: "products", fileNameProperty: "imageName")]
    private ?File $imageFile = null;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    }
}
```

If the bundle's configuration parameter `inject_on_load` is set to `true` the `Product::setImageFile()`
method above must take an instance of `File` as when this class is hydrated by Doctrine this
bundle will automatically inject an instance of `File` there. However, if you were to change
the image path to a new image in that instance of `File` and attempted a `flush()` nothing
would happen, instead inject a new instance of `UploadedFile` with the new path to your new
image to sucessfully trigger the upload.

**N.B.** : UploadedFile objects have a
[*test* mode](https://github.com/symfony/symfony/blob/6.1/src/Symfony/Component/HttpFoundation/File/UploadedFile.php#L63)
that can be used to simulate file uploads.
If you are going to insert images programmatically, make sure to set it to true, i.e.

``` php
<?php

$uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile($filePath, basename($filePath), null, null, true);
$entity->setFile( $uploadedFile );
```

Be aware that these files will be _moved_ to the designated location by VichUploader, so if you want to keep the
original files intact, copy them to a temporary location first. If you plan to upload the same file multiple times,
you will need multiple different locations, otherwise the handler on the first VichUploader field will move the file
and accessing that file will fail on subsequent tries.
Note: The `UploadedFile` constructor changed in 4.1, if you're using a prior version, you will need to add a parameter
for the file size.

## Failed to set metadata before uploading the file

When using Gaufrette with some specific adapters, it's possible that metadata can't be set when uploading a file.
For some adapters, metadata need to be defined after the upload because setting the metadata for a given file results
in a separate API call. In the other hand, for other adapters setting the metadata does not result in an API call: the
metadata is joined with the file during the upload.
To summarize, [Gaufrette support for metadata is flawed](https://github.com/KnpLabs/Gaufrette/issues/108)
(see issue [GH-163](https://github.com/dustin10/VichUploaderBundle/issues/163)).

## Doctrine/annotations package required when using doctrine-bundle >= 2.8

If your project is using `doctrine-bundle:>=2.8`, you must require `doctrine/annotations` package from
your project as it is not required in `doctrine-bundle` anymore from this version.  
This bundle is using a `Reader` interface from this package in order to work for both annotations
and attributes mapping.
