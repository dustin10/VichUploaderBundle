Configuration Reference
=======================

## Configuration Reference

Below is the full default configuration for the bundle:

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver:  orm # or mongodb or propel
    twig:       true
    gaufrette:  false # set to true to enable gaufrette support
    flysystem:  false # set to true to enable flysystem support
    storage:    vich_uploader.storage.file_system
    mappings:
        product_image:
            uri_prefix:         web   # uri prefix to resource
            upload_destination: ~     # gaufrette storage fs id, required
            namer:              ~     # specify a file namer service id for this entity, null default
            directory_namer:    ~     # specify a directory namer service id for this entity, null default
            delete_on_remove:   true  # determines whether to delete file upon removal of entity
            inject_on_load:     false  # determines whether to inject a File instance upon load
        # ... more mappings
```

- `storage`: The id of the storage service used by the bundle to
store files. The bundle ships with vich_uploader.storage.file_system,
vich_uploader.storage.gaufrette (see [FileSystemStorage VS GaufretteStorage](#filesystemstorage-vs-gaufrettestorage))
and vich_uploader.storage.flysystem.
