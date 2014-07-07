# Flysystem configuration

Flysystem's configuration looks a lot like Gaufrette's.
Here is a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details on this topic you
should refer to the official documentation).

``` yaml
oneup_flysystem:
    adapters:
        product_adapter:
            local:
                directory: %kernel.root_dir%/../web/images/products

    filesystems:
        product_image_fs:
            adapter:    product_adapter

vich_uploader:
    db_driver: orm
    flysystem: true
    storage:   vich_uploader.storage.flysystem
    mappings:
        product_image:
            uri_prefix:         /images/products
            upload_destination: product_image_fs
```

Using vich_uploader.storage.flysystem as the storage service you can still use
the same mappings options that you would use with default storage.

**Note:**

> In this case upload_destination refer to a Flysystem filesystem and
> directory_namer should be used to generate a valid filesystem ID (and not a
> real path). See more about this in [Namers section](../usage.md#namers)

**Note:**

> [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle) needs
> to be installed and activated to get the FlysystemStorage to work.
