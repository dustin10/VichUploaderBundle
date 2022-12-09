# XML mappings

You can choose to describe your entities in XML. This bundle supports this
format and comes with the following syntax to declare your uploadable fields:

```xml
<!-- config/vich_uploader/Entity.Product.xml -->
<!-- Attributes "mapping", "name" and "filename_property" are required -->
<vich_uploader xmlns="https://vich-uploader-bundle/schema/"
               class="Acme\DemoBundle\Entity\Product">
    <field mapping="products" name="imageFile" filename_property="imageName"
           size="imageSize" dimensions="imageDimensions" mime_type="imageMimeType" original_name="imageOriginalName" />
</vich_uploader>
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

**N.B:**

> In order to be able to use this format, make sure that the `simplexml`
> extension is enabled (it is by default).

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
