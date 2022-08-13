# Gaufrette

> Gaufrette is a PHP library that provides a filesystem abstraction layer.

**N.B.**: although Gaufrette is a well-known library, please note that it hasn't been updated
in a while and that there are still lots of unresolved issues. One of them being a broken metadata
implementation, [causing us troubles](../known_issues.md#failed-to-set-metadata-before-uploading-the-file).

## Configuration

In order to use Gaufrette you have to configure it. Here is
a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details
on this topic you should refer to the [gaufrette documentation](https://github.com/KnpLabs/KnpGaufretteBundle)).

``` yaml
knp_gaufrette:
    stream_wrapper: ~

    adapters:
        product_adapter:
            local:
                directory: '%kernel.project_dir%/public/images/products'

    filesystems:
        products_fs:
            adapter: product_adapter

vich_uploader:
    db_driver: orm
    storage: gaufrette

    mappings:
        products:
            uri_prefix: /images/products
            upload_destination: products_fs
```

Using `Vich\UploaderBundle\Storage\GaufretteStorage` as the storage service,
you can still use the same mappings options that you would
use with default storage.

**Note:**

> Make sure that Gaufrette stream wrapper overloading is enabled.

**Note:**

> In this case `upload_destination` refers to a gaufrette filesystem.

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
