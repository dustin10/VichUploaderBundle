# Upgrading from v2.9 to v3.0

## PHP and Symfony version requirements

* PHP version requirement raised from `^8.1` to `^8.3`. You must upgrade to PHP 8.3 or higher.
* Symfony version requirement raised from `^5.4 || ^6.0 || ^7.0` to `^6.4 || ^7.4 || ^8.0`. Symfony 5.x support has been dropped.

## Breaking Changes

* The deprecated `Vich\UploaderBundle\Mapping\Annotation` namespace has been removed. Use `Vich\UploaderBundle\Mapping\Attribute` instead.
* The deprecated `AnnotationInterface` has been removed. Use `AttributeInterface` instead.
* `AttributeReader` deprecated methods have been removed: use `getClassAttribute()` instead of `getClassAnnotation()`, `getPropertyAttribute()` instead of `getPropertyAnnotation()`.
* `NamerInterface::name()` now requires `object|array` as first argument (was `object`) and `PropertyMappingInterface` as second argument (was `PropertyMapping`). Update your custom namers accordingly.
* `DirectoryNamerInterface::directoryName()` now requires `PropertyMappingInterface` as second argument (was `PropertyMapping`). Update your custom directory namers accordingly.
* `FileInjectorInterface::injectFile()` now accepts `array|object` as first argument (was `object`) and `PropertyMappingInterface` as second argument (was `PropertyMapping`).
* `StorageInterface::remove()` has a new optional third parameter `?string $dir = null`. Custom storage implementations must add this parameter to their `remove()` method signature.
* `StorageInterface` has a new method `listFiles(PropertyMappingInterface $mapping): iterable`. Custom storage implementations must implement this method.
* Several classes are now `readonly` (requires PHP 8.2+, which is covered by the PHP 8.3 requirement): `MetadataReader`, `AttributeReader`, `CacheWarmer`, `Events`, `ClassUtils`, `PropertyPathUtils`, `PHPCRAdapter`, `DoctrineORMAdapter`, `MongoDBAdapter`, `StoredFile`. If you extend any of these classes, you must make the extending class also `readonly` or remove the extension.
* MongoDB ODM adapter tests have been removed. If you use MongoDB ODM, be aware that its test coverage has been reduced.

## New Features

* New `PropertyMappingInterface` introduced. The `$mapping` parameter in `NamerInterface` and `DirectoryNamerInterface` now uses this interface instead of the concrete `PropertyMapping` class, allowing easier testing and custom implementations.
* New `PropertyMappingFactoryInterface` introduced for easier testing of code that depends on `PropertyMappingFactory`.
* New `MetadataReaderInterface` introduced for easier testing of code that depends on `MetadataReader`.
* New `DownloadHandlerInterface` and `UploadHandlerInterface` introduced for easier testing of code that depends on the handlers.
* New `vich:cleanup` console command to detect and delete uploaded files no longer referenced in the database.
* New `ChainDirectoryNamer` to combine multiple directory namers.

# Upgrading from v2.8 to v2.9

## Deprecations

* The `Vich\UploaderBundle\Mapping\Annotation` namespace is deprecated. Replace it with `Vich\UploaderBundle\Mapping\Attribute`;
  The old namespace will be removed in version 3.0.
* `AttributeReader` methods: replace `*Annotation()` with `*Attribute()` (e.g., `getClassAnnotation()` → `getClassAttribute()`).

## New Features

* New `namer_keep_extension` configuration option to force namers to preserve original file extension.
* Custom namers using `namer_keep_extension: true` must implement `ConfigurableInterface`.

# Upgrading from v2.7 to v2.8

* Namers are not public anymore. If you uses a custom namer, you can now make it private.

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
* all methods arguments are now fully type-hinted
* all methods have now return types
* all constructors now use property promotion
* all deprecated features were removed
* the new default type for mapping is "attribute". You can still use annotations, but you need an explicit definition (set "annotation" as value for "vich_uploader.metadata.type" config key)
* the service "vich_uploader.current_date_time_helper" has been removed. The `DateTimeHelper` interface has been
  removed as well.
