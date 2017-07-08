Usage
=====

This guide will show you how to handle a file upload, store the file on the file
system (or on some remote server if you prefer) and persist the stored filename
to the database.

Here is a summary of what you will have to do:

  * [configure an upload mapping](#step-1-configure-an-upload-mapping) ;
  * [link the upload mapping to an entity](#step-2-link-the-upload-mapping-to-an-entity) ;
  * [configure the lifecycle events](#step-3-configure-the-lifecycle-events-optional-step) (optional step).

**Note:**

> Throughout the guide we will use Doctrine ORM as the persistence engine on
> the examples. Though mostly, there won't be much difference if you use a
> different engine.

## Step 1: configure an upload mapping

Each time you need to upload something new to your system, you'll start by
configuring where it should be stored (`upload_destination`), the web path to
that directory (`uri_prefix`) and give the upload mapping a name
(`product_image`in our example).

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver: orm

    mappings:
        product_image:
            uri_prefix:         /images/products
            upload_destination: '%kernel.root_dir%/../web/images/products'
```

This is the minimal amount of configuration needed in order to describe a
working mapping.

**Note:**
> The default behaviour is using the original name of the uploaded file, it can
> override an old file with the same name. Please use a [namer](namers.md)
> like `vich_uploader.namer_property` to avoid this issue.


## Step 2: link the upload mapping to an entity

The final step is to create a link between the filesystem and the entity you
want to make uploadable.

We already created an abstract representation of the filesystem (the mapping),
so we just have to tell the bundle which entity should use which mapping. In
this guide we'll use annotations to achieve this but you can also use
[YAML](mapping/yaml.md) or [XML](mapping/xml.md).

First, annotate your class with the `Uploadable` annotation. This is really like
a flag indicating that the entity contains uploadable fields.

Next, you have to create the two fields needed for the bundle to work:

  1. create a field (e.g. `imageName`) which will be stored to the database as a
     string. This will hold the filename of the uploaded file.
  2. create another field (e.g. `imageFile`). This will store the `UploadedFile`
     object after the form is submitted. This should *not* be persisted to the
     database, but you *do* need to annotate it.

The `UploadableField` annotation has a few options. They are as follows:

  * `mapping`: required, the mapping name specified in the bundle configuration to use;
  * `fileNameProperty`: required, the property that will contain the name of the uploaded file;
  * `size`: the property that will contain the size in bytes of the uploaded file;
  * `mimeType`: the property that will contain the mime type of the uploaded file;
  * `originalName`: the property that will contain the original name of the uploaded file.

**Note**:

> Annotations can NOT be used in conjunction with Propel. You must describe your
> mappings in [YAML](mapping/yaml.md) or [XML](mapping/xml.md).

Lets look at an example using a fictional `Product` ORM entity:

``` php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // ..... other fields

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName", size="imageSize")
     * 
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $imageSize;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Product
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
        
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param string $imageName
     *
     * @return Product
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
        
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageName()
    {
        return $this->imageName;
    }
    
    /**
     * @param integer $imageSize
     *
     * @return Product
     */
    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
        
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getImageSize()
    {
        return $this->imageSize;
    }
}
```

Alternatively you can use `Vich\UploaderBundle\Entity\File` embeddable for storing file info in your ORM entity:

``` php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // ..... other fields

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="image.name", size="image.size", mimeType="image.mimeType", originalName="image.originalName")
     * 
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     *
     * @var EmbeddedFile
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;
    
    public function __construct()
    {
        $this->image = new EmbeddedFile();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $image
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param EmbeddedFile $image
     */
    public function setImage(EmbeddedFile $image)
    {
        $this->image = $image;
    }

    /**
     * @return EmbeddedFile
     */
    public function getImage()
    {
        return $this->image;
    }
}
```

**Important:**
> If you use Doctrine, you need to pay attention to the comment on `setImageFile` method in the above example.
> If you miss that, you won't be able to update your file.

**Note:**

> Don't forget to clear the cache once your entity is configured: `php app/console cache:clear`


## Step 3: configure the lifecycle events (optional step)

Even if the previous mapping is fully working, you might want to customize the
behavior to adopt when your entities are hydrated, updated or removed. For
instance: should the files be updated or removed accordingly?

Three simple configuration options allow you to fit your application's needs.

``` yaml
vich_uploader:
    db_driver: orm
    mappings:
        product_image:
            uri_prefix:         /images/products
            upload_destination: %kernel.root_dir%/../web/images/products

            inject_on_load:     false
            delete_on_update:   true
            delete_on_remove:   true
```

All options are listed below:

  * `delete_on_remove`: default `true`, should the file be deleted when the entity is removed ;
  * `delete_on_update`: default `true`, should the file be deleted when a new file is uploaded ;
  * `inject_on_load`: default `false`, should the file be injected into the uploadable entity
    when it is loaded from the data store. The object will be an instance of
    `Symfony\Component\HttpFoundation\File\File`.

**Note:**

> The values used for the last three configuration options are the default ones.


## That was it!

You're done! Now create a form with an `imageFile` field that uses the `file`
type.
When you submit and save, the uploaded file will automatically be moved to the
location you configured and the `imageName` field will be set to the filename of
the uploaded file.

Feel free to check our [sandbox application](https://github.com/K-Phoen/Vich-Uploader-Sandbox)
if you need working examples!

[Return to the index to explore the other possibilities of the bundle.](index.md)
