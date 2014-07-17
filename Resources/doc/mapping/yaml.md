YAML mappings
=============

You can choose to describe your entities in YAML. This bundle supports this
format and comes with the following syntax to declare your uploadable fields:

```yaml
# src/Acme/DemoBundle/Resources/config/vich_uploader/Product.yml
Acme\DemoBundle\Entity\Product:
    image:
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
