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


## Annotations don't work with Propel

When Propel is the chosen database driver, the "uploadable" entities must be
known when the service container is built. As there is no way to retrieve all
annotated entities, the only workaround is to define mappings using Yaml or XML.
