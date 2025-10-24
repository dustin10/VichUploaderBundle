# Upgrading from v2.8 to v2.9

## Deprecations

* The `Vich\UploaderBundle\Mapping\Annotation` namespace is deprecated. Replace it with `Vich\UploaderBundle\Mapping\Attribute`;
  The old namespace will be removed in version 3.0.
* `AttributeReader` methods: replace `*Annotation()` with `*Attribute()` (e.g., `getClassAnnotation()` → `getClassAttribute()`).

## New Features

* New `namer_keep_extension` configuration option to force namers to preserve original file extension.
* Custom namers using `namer_keep_extension: true` must implement `ConfigurableInterface`.

# Upgrading from v2.7 to v2.8

* Namers are not public anymore. If you use a custom namer, you can now make it private.

# Upgrading from v2.6 to v2.7

* Now the original extension '.xlsb' is retained even if the mime type is guessed as 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'.
  
# Upgrading from v2.4 to v2.5

* To address the question raised in the previous version, now the original extension '.csv' is retained
  even if the mime type is guessed as 'text/plain'.

# Upgrading from v2.3 to v2.4

* To address a security question, the original extension of the uploaded file is not preserved anymore.
  Instead, it is replaced by the extension of the matching mime type. This could cause a different
  behaviour only if you use some non-standard extension, otherwise it should not change anything.

# Upgrading from v2.1 to v2.2

* The signature of `StorageInterface::resolveStream` method was changed. The $fieldName parameter is now nullable. 
* the `AdapterInterface` no longer requires `getObjectFromArgs` method.
* the `AdapterInterface::recomputeChangeSet()` accepts `Doctrine\Persistence\Event\LifecycleEventArgs` as argument.

# Upgrading from v2.0 to v2.1

* the internal class `FilenameUtils` has been removed.

# Upgrading from v1 to v2.0

* every class marked as `@final` is now final
* all properties are now fully type-hinted
* all method arguments are now fully type-hinted
* all methods have now return types
* all constructors now use property promotion
* all deprecated features were removed
* the new default type for mapping is "attribute". You can still use annotations, but you need an explicit definition (set "annotation" as value for "vich_uploader.metadata.type" config key)
* the service "vich_uploader.current_date_time_helper" has been removed. The `DateTimeHelper` interface has been
  removed as well.
