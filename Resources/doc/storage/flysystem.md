Flysystem
=========

> Flysystem is a filesystem abstraction which allows you to easily swap out a
> local filesystem for a remote one.


## Configuration

Here is a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details on this topic you
should refer to [the official documentation](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/index.md)).

``` yaml
oneup_flysystem:
    adapters:
        product_adapter:
            local:
                directory: %kernel.root_dir%/../web/images/products

    filesystems:
        product_image_fs:
            adapter:    product_adapter
            mount:      product_image_fs

vich_uploader:
    db_driver: orm

    flysystem: true
    storage:   vich_uploader.storage.flysystem

    mappings:
        product_image:
            uri_prefix:         /images/products
            upload_destination: product_image_fs
```

Using `vich_uploader.storage.flysystem` as the storage service you can still use
the same mappings options that you would use with default storage.

**Note:**

> In this case `upload_destination` refers to a Flysystem FS.

**Note:**

> [OneupFlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle) needs
> to be installed and activated to get the FlysystemStorage to work.


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
