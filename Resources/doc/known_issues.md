Known issues
============

## The file is not updated if there are not other changes in the entity

As the bundle is listening to Doctrine `prePersist` and `preUpdate` events, which are not fired
when there is no change on field mapped by Doctrine, the file upload is not handled if the image field
is the only updated.

A workaround to solve this issue is to manually generate a change:

```
class Product
{
    // ...

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime $updatedAt
     */
    protected $updatedAt;

    // ...

    public function setImage($image)
    {
        $this->image = $image;

        if ($this->image) {
            $this->updatedAt = new \DateTime('now');
        }
    }
}
```
See issue [GH-123](https://github.com/dustin10/VichUploaderBundle/issues/123)

## Catchable Fatal Error

When you get the following error:`Catchable Fatal Error: ... must be an instance of Symfony\Component\HttpFoundation\File\UploadedFile, string given, ...` 
you have to define   
`{{ form_enctype(upload_form) }}` in your form. 

This is needed for Symfony versions older than 2.3.

## Annotations don't work with Propel

When Propel is the chosen database driver, the "uploadable" entities must be
known when the service container is built. As there is no way to retrieve all
annotated entities, the only workaround is to define mappings using Yaml or XML.

## Image not deleted with cascade deletion and Doctrine

Just check the following options: ```cascade={"remove"}``` and ```orphanRemoval=true```.

```yaml
Vendor\Bundle\CoolBundle\Entity\CoolEntity:
    type:   entity

    fields: ...

    oneToMany:
        images:
           targetEntity:   CoolEntityImage
           mappedBy:       bike
           cascade:        [persist, merge, remove]
           orphanRemoval:  true
```

## No Upload is triggered when manually injecting an instance of Symfony\Component\HttpFoundation\File\File

An upload will only be triggered if an instance of `Symfony\Component\HttpFoundation\File\UploadedFile`
is injected into the setter for the `Vich\UploadableField` property of your class. Normally this is done
automatically if using Symfony Form but if your application logic is attempting to do this manually and you
inject an instance of `Symfony\Component\HttpFoundation\File\File` *instead* the bundle will silently ignore
your attempted upload.
Consider the following class:

``` php
<?php
/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Product
{
    /**
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName")
     *
     * @var \Symfony\Component\HttpFoundation\File\File $image
     */
    protected $image;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param \Symfony\Component\HttpFoundation\File\File $image
     */
    public function setImage(File $image)
    {
        $this->image = $image
    }
}
```

If the bundle's configuration parameter `inject_on_load` is set to `true` the `Product::setImage()`
method above must take an instance of `File` as when this class is hydrated by Doctrine this
bundle will automatically inject an instance of `File` there. However if you were to change
the image path to a new image in that instance of `File` and attempted a `flush()` nothing
would happen, instead inject a new instance of `UploadedFile` with the new path to your new
image to sucessfully trigger the upload.
