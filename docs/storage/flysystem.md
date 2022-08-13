# Flysystem

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

To install the bundle, run the following command:

```bash
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
        products:
            uri_prefix: /images/products
            upload_destination: default.storage # Use the name you defined for your storage here
```

## Integrating with [oneup/flysystem-bundle](https://github.com/1up-lab/OneupFlysystemBundle)

To install the bundle, run the following command:

```bash
composer require oneup/flysystem-bundle
```

Here is a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details on this topic you
should refer to
[the bundle documentation](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/doc/index.md)).

``` yaml
oneup_flysystem:
    adapters:
        product_adapter:
            local:
                location: '%kernel.project_dir%/public/images/products'

    filesystems:
        products:
            adapter: product_adapter
            mount: products

vich_uploader:
    db_driver: orm
    storage: flysystem

    mappings:
        products:
            uri_prefix: /images/products
            # For Oneup/FlysystemBundle v4
            upload_destination: oneup_flysystem.products_filesystem
            # For Oneup/FlysystemBundle v3
            # upload_destination: products
```

## That was it!

Check out the docs for information on how to use the bundle!
[Return to the index.](../index.md)
