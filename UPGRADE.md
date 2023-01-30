# Upgrading from v2.0.1 to v2.1.0

* the internal class `FilenameUtils` has been removed.

# Upgrading from v1 to v2.0.0

* every class marked as `@final` is now final
* all properties are now fully type-hinted
* all methods arguments are now fully type-hinted
* all methods have now return types
* all constructors now use property promotion
* all deprecated features were removed
* the new default type for mapping is "attribute". You can still use annotations, but you need an explicit definition (set "annotation" as value for "vich_uploader.metadata.type" config key)
* the service "vich_uploader.current_date_time_helper" has been removed. The `DateTimeHelper` interface has been
  removed as well.
* if your project use `doctrine-bundle` >= 2.8 version, you must require `doctrine/annotations` in order to use annotations or attributes for mapping