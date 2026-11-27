# Configuration Reference

Below is the full default configuration for the bundle:

``` yaml
# config/packages/vich_uploader.yaml or app/config/config.yml
vich_uploader:
    db_driver: orm        # or mongodb or phpcr - default db driver
    twig: true            # set to false to disable twig integration
    form: true            # set to false to disable form integration
    storage: file_system  # or gaufrette or flysystem
    metadata:
        auto_detection: true
        cache: file
        type: attribute
    mappings:
        products:
            uri_prefix: /uploads         # uri prefix to resource
            upload_destination: ~        # gaufrette storage fs id, required
            namer: ~                     # specify a file namer service for this entity, null default
            directory_namer: ~           # specify a directory namer service for this entity, null default
            delete_on_remove: true       # determine whether to delete file upon removal of entity
            erase_fields: true           # set to false if you have non-nullable fields for mapping
            delete_on_update: true       # determine whether to delete the file upon update of entity
            inject_on_load: false        # determine whether to inject a File instance upon load
            namer_keep_extension: false  # force namers to keep original file extension
                                         # WARNING: Always validate file extensions when set to true to prevent security risks!
            db_driver: phpcr             # override the default db driver set above. Allow separate driver per mapping
        # ... more mappings
```

The reference can be dumped using the following command: `php bin/console debug:config vich_uploader`

## Metadata cache

`metadata.cache` controls how the bundle caches the mapping metadata it reads from your
entities. It is not related to Symfony's `framework.cache` configuration, so pointing your
application's cache pools at Redis/Valkey (or anything else) has no effect on it by itself.
It accepts one of the following values:

* `file` (the default): metadata is cached on disk, in the directory configured via
  `metadata.file_cache.dir` (see below).
* `none`: metadata caching is disabled.
* a service id: any service implementing `Metadata\Cache\CacheInterface` can be used instead,
  for example to store the metadata in one of your application's own cache pools.

### `metadata.file_cache.dir`

Only used when `metadata.cache` is set to `file`. It is the directory the metadata cache files
are written to, and defaults to `%kernel.cache_dir%/vich_uploader`:

``` yaml
vich_uploader:
    metadata:
        cache: file
        file_cache:
            dir: '%kernel.cache_dir%/vich_uploader'
```

### Using a PSR-6 cache pool (e.g. Redis/Valkey)

To have the metadata cache stored in a PSR-6 cache pool, such as a Redis/Valkey-backed
`framework.cache` pool, wrap that pool with `Metadata\Cache\PsrCacheAdapter` and point
`metadata.cache` at the resulting service:

``` yaml
# config/services.yaml
services:
    app.vich_metadata_cache:
        class: Metadata\Cache\PsrCacheAdapter
        arguments:
            - 'vich_' # cache key prefix
            - '@cache.app' # any PSR-6 cache pool

# config/packages/vich_uploader.yaml
vich_uploader:
    metadata:
        cache: app.vich_metadata_cache
```

With this configuration, nothing is written to `metadata.file_cache.dir` any more; the
metadata is stored in the `cache.app` pool instead.
