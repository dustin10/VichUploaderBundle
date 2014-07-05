# Gaufrette configuration

In order to use Gaufrette you have to configure it. Here is
a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details
on this topic you should refer to the gaufrette documentation).

``` yaml
knp_gaufrette:
    stream_wrapper: ~

    adapters:
        product_adapter:
            local:
                directory: %kernel.root_dir%/../web/images/products

    filesystems:
        product_image_fs:
            adapter:    product_adapter

vich_uploader:
    db_driver: orm
    gaufrette: true
    storage:   vich_uploader.storage.gaufrette
    mappings:
        product_image:
            uri_prefix:         /images/products
            upload_destination: product_image_fs
```

Using vich_uploader.storage.gaufrette as the storage service
you can still use the same mappings options that you would
use with default storage.

**Note:**

> Make sure that Gaufrette stream wrapper overloading is enabled.

**Note:**

> In this case upload_destination refer to a gaufrette filesystem
> and directory_namer should be used to generate a valid
> filesystem ID (and not a real path). See more about this
> in [Namers section](../usage.md#namers)



