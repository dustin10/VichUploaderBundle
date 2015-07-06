YAML mappings
=============

You can choose to describe your entities in YAML. This bundle supports this
format and comes with the following syntax to declare your uploadable fields:

Here's how your entity could look like:

```yaml
Acme\DemoBundle\Entity\Product:
    type: entity
    # ...
    fields:
        # Product::$imageName will hold the reference to the upload
        imageName:
            type: string
            column: image_name
```

And here's how you should configure the uploader:

```yaml
# src/Acme/DemoBundle/Resources/config/vich_uploader/Product.yml
Acme\DemoBundle\Entity\Product:
    imageName:
        mapping:           product_image
        filename_property: image_name
```

To be found, the mapping configuration MUST be in the `Resources/config/vich_uploader`
directory of the bundle containing the entity you want to describe.

**N.B:**

> In order to be able to use this format, make sure that the `symfony/yaml`
> package is installed.


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
