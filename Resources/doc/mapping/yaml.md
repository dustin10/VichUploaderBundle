YAML mappings
=============

You can choose to describe your entities in YAML. This bundle supports this
format and comes with the following syntax to declare your uploadable fields:

```yaml
# src/Acme/DemoBundle/Resources/config/vich_uploader/Entity.Product.yml
Acme\DemoBundle\Entity\Product:
    image:
        mapping:           product_image # required
        filename_property: imageName     # required
        size:              imageSize
        mime_type:         imageMimeType
        original_name:     imageOriginalName
```

To be automatically found, the mapping configuration MUST be in the `Resources/config/vich_uploader`
directory of the bundle containing the entity you want to describe.

If you need the mapping elsewhere, you need to add some configuration.
In the following example, the configuration is placed in the `app/config/vich_uploader` directory:

```yaml
# app/config/config.yml
vich_uploader:
    # ...
    metadata:
        auto_detection: false
        directories:
            - {path: '%kernel.root_dir%/config/vich_uploader', namespace_prefix: 'Acme'}
```

#### Naming YAML Files

The `namespace_prefix` parameter, combined with the .yml file name in `config/vich_uploader` must combine to form the FQCN of your entity. For example an entity of `MyApp\MyBundle\Entity\Customer` should be configured using either of the following:

`namespace_prefix: 'MyApp\MyBundle'` and then have a config file `Entity.Customer.yml`

`namespace_prefix: 'MyApp\MyBundle\Entity'` and then have a config file `Customer.yml`

**N.B:**

> In order to be able to use this format, make sure that the `symfony/yaml`
> package is installed.

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
