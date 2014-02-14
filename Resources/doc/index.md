VichUploaderBundle
==================

The VichUploaderBundle is a Symfony2 bundle that attempts to ease file
uploads that are attached to an entity. The bundle will automatically name and
save the uploaded file according to the configuration specified on a per-property
basis using a mix of configuration and annotations. After the entity has been created
and the file has been saved, if configured to do so an instance of
`Symfony\Component\HttpFoundation\File\File` will be loaded into the annotated property
when the entity is loaded from the datastore. The bundle also provides templating helpers
for generating URLs to the file. The file can also be configured to be removed from the
file system upon removal of the entity.

The bundle provides different ways to interact with the filesystem; you can choose
your preferred one by configuration. Basically, you can choose to work with the local
filesystem or integrate gaufrette to have nice abstraction over the filesystem (for more
info see [FileSystemStorage VS GaufretteStorage](usage.md#filesystemstorage-vs-gaufrettestorage)).

You can also implement your own `StorageInterface` if you need to.


## Documentation

  1. [Installation](installation.md)
  2. [Usage](usage.md)
  3. [Configuration reference](configuration_reference.md)
  4. [Known issues](known_issues.md)
