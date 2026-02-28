> Upgrade notes for earlier versions (v1 → v2.x) can be found in the
> [2.x branch](https://github.com/dustin10/VichUploaderBundle/blob/2.x/UPGRADE.md).

# Upgrading from v2.9 to v3.0

## Removed deprecated features

* The `Vich\UploaderBundle\Mapping\Annotation` namespace has been removed. Use `Vich\UploaderBundle\Mapping\Attribute` instead.
* The deprecated `AnnotationInterface` has been removed. Use `AttributeInterface` instead.
* `AttributeReader` deprecated methods have been removed: use `getClassAttribute()` instead of `getClassAnnotation()`, `getPropertyAttribute()` instead of `getPropertyAnnotation()`.

## BC Breaks

### `NamerInterface::name()` now accepts `object|array`

The `name()` method in `Vich\UploaderBundle\Naming\NamerInterface` now declares
`object|array` for its first parameter instead of just `object`. Any custom namer
that implements this interface must update its signature accordingly:

```php
// Before
public function name(object $object, PropertyMapping $mapping): string

// After
public function name(object|array $object, PropertyMapping $mapping): string
```

### New `UploadHandlerInterface`

`Vich\UploaderBundle\Handler\UploadHandlerInterface` has been introduced. The
`upload()`, `inject()`, `clean()` and `remove()` methods are now part of this
interface. `UploadHandler` implements it.

If you type-hint `Vich\UploaderBundle\Handler\UploadHandler` in your own services
or event listeners, consider switching to `UploadHandlerInterface` instead.

### New `PropertyMappingResolverInterface`

`Vich\UploaderBundle\Mapping\PropertyMappingResolverInterface` has been introduced.
It exposes a single `resolve()` method. If you have a custom implementation of the
mapping resolver, make it implement this interface.

# Upgrading from v2.8 to v2.9

## Deprecations

* The `Vich\UploaderBundle\Mapping\Annotation` namespace is deprecated. Replace it with `Vich\UploaderBundle\Mapping\Attribute`;
  The old namespace will be removed in version 3.0.
* `AttributeReader` methods: replace `*Annotation()` with `*Attribute()` (e.g., `getClassAnnotation()` → `getClassAttribute()`).

## New Features

* New `namer_keep_extension` configuration option to force namers to preserve original file extension.
* Custom namers using `namer_keep_extension: true` must implement `ConfigurableInterface`.
