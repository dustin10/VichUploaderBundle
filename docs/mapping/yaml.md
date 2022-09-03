# YAML mappings

You can choose to describe your entities in YAML. This bundle supports this
format and comes with the following syntax to declare your uploadable fields:

```yaml
# config/vich_uploader/Entity.Product.yaml
Acme\DemoBundle\Entity\Product:
    imageFile:
        mapping: products             # required
        filename_property: imageName  # required
        size: imageSize
        mime_type: imageMimeType
        original_name: imageOriginalName
        dimensions: imageDimensions
```

To be automatically found, the mapping configuration MUST be in the `config/vich_uploader` directory
of your symfony application, and the root namespace MUST be the de-facto standard `App` namespace.
Third-party bundles providing configuration must follow the same rule in their own directory.

Turning off auto-detection will disable this discovery method:

```yaml
# config/packages/vich_uploader.yaml or app/config/config.yml
vich_uploader:
    # ...
    metadata:
        auto_detection: false
```

If you need the mapping elsewhere, you need to add some configuration.
In the following example, the configuration is placed in the `config/acme` directory:

```yaml
# config/packages/vich_uploader.yaml or app/config/config.yml
vich_uploader:
    # ...
    metadata:
        auto_detection: false # omit this line if the previously described auto-discovery is still needed
        directories:
            - {path: '%kernel.project_dir%/config/acme', namespace_prefix: 'Acme'}
```

## Naming YAML Files

The `namespace_prefix` parameter, combined with the .yaml file name in `config/vich_uploader` must
combine to form the FQCN of your entity. For example an entity of `MyApp\MyBundle\Entity\Customer`
should be configured using either of the following:

`namespace_prefix: 'MyApp\MyBundle'` and then have a config file `Entity.Customer.yaml`

`namespace_prefix: 'MyApp\MyBundle\Entity'` and then have a config file `Customer.yaml`

**N.B:**

> In order to be able to use this format, make sure that the `symfony/yaml`
> package is installed.

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
