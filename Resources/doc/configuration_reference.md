Configuration Reference
=======================

## Configuration Reference

Below is the full default configuration for the bundle:

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver:  orm         # or mongodb or propel or phpcr - default db driver
    templating: true        # set to false to disable templating integration 
    twig:       true        # set to false to disable twig integration (requires templating)                  
    form:       true        # set to false to disable form integration
    storage:    file_system # or gaufrette or flysystem
    metadata:
        auto_detection: true
        cache:          file
    mappings:
        product_image:
            uri_prefix:         /uploads # uri prefix to resource
            upload_destination: ~        # gaufrette storage fs id, required
            namer:              ~        # specify a file namer service for this entity, null default
            directory_namer:    ~        # specify a directory namer service for this entity, null default
            delete_on_remove:   true     # determines whether to delete file upon removal of entity
            delete_on_update:   true     # determines wheter to delete the file upon update of entity
            inject_on_load:     false    # determines whether to inject a File instance upon load
            db_driver:          phpcr    # overides the default db driver set above. Allows seperate driver per mapping
        # ... more mappings
```

The reference can be dumped using the following command: `php bin/console debug:config vich_uploader`
