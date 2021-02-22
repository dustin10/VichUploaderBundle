Flysystem
=========

> Flysystem is a filesystem abstraction library for PHP. It provides an abstraction 
> for the filesystem in order to change the storage backend depending on the execution 
> environment (local files in development, cloud storage in production and memory in tests)
> and your configuration.

[Read the official library documentation](https://flysystem.thephpleague.com)

VichUploaderBundle can use Flysystem as a storage engine by relying on one of two bundles: 
[thephpleague/flysystem-bundle](https://github.com/thephpleague/flysystem-bundle)
or
[oneup/flysystem-bundle](https://github.com/1up-lab/OneupFlysystemBundle).

**Note:**

> When using `flysystem` as the storage engine, you can still use
> the same mappings options that you would use with default storage.

## Integrating with [thephpleague/flysystem-bundle](https://github.com/thephpleague/flysystem-bundle)

**Note:**

> Since VichUploaderBundle v1.17, version 2 of bundle is supported. Version 1 is supported until VichUploaderBundle v1.16.

To install the bundle, run the following command:

```
composer require league/flysystem-bundle
```

It will create a default YAML configuration using Symfony Flex:

```yaml
# config/packages/flysystem.yaml

flysystem:
    storages:
        default.storage:
            adapter: 'local'
            options:
                directory: '%kernel.project_dir%/var/storage/default'
```

You can adapt this configuration to your needs by reading the 
[bundle documentation](https://github.com/thephpleague/flysystem-bundle/blob/master/docs/1-getting-started.md).

Once you have a storage ready, you can use it in your VichUploaderBundle configuration:

``` yaml
vich_uploader:
    db_driver: orm
    storage: flysystem

    mappings:
        product_image:
            uri_prefix: /images/products
            upload_destination: default.storage # Use the name you defined for your storage here
```

## Integrating with [oneup/flysystem-bundle](https://github.com/1up-lab/OneupFlysystemBundle)

**Note:**

> Since VichUploaderBundle v1.17, version 4 of bundle is supported. Version 3 is supported until VichUploaderBundle v1.16.

To install the bundle, run the following command:

```
composer require oneup/flysystem-bundle
```

Here is a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details on this topic you
should refer to
[the bundle documentation](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/docs/index.md)).

``` yaml
oneup_flysystem:
    adapters:
        product_adapter:
            local:
                directory: '%kernel.project_dir%/public/images/products'

    filesystems:
        product_image_fs:
            adapter: product_adapter
            mount: product_image_fs

vich_uploader:
    db_driver: orm
    storage: flysystem

    mappings:
        product_image:
            uri_prefix: /images/products
            upload_destination: product_image_fs
```

## That was it!

Check out the docs for information on how to use the bundle!
[Return to the index.](../index.md)
