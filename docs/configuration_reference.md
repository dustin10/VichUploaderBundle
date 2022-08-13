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
        type: attribute    # or annotation
    mappings:
        products:
            uri_prefix: /uploads    # uri prefix to resource
            upload_destination: ~   # gaufrette storage fs id, required
            namer: ~                # specify a file namer service for this entity, null default
            directory_namer: ~      # specify a directory namer service for this entity, null default
            delete_on_remove: true  # determine whether to delete file upon removal of entity
            delete_on_update: true  # determine wheter to delete the file upon update of entity
            inject_on_load: false   # determine whether to inject a File instance upon load
            db_driver: phpcr        # override the default db driver set above. Allow separate driver per mapping
        # ... more mappings
```

The reference can be dumped using the following command: `php bin/console debug:config vich_uploader`
