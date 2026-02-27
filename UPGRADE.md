> Upgrade notes for earlier versions (v1 → v2.x) can be found in the
> [2.x branch](https://github.com/dustin10/VichUploaderBundle/blob/2.x/UPGRADE.md).

# Upgrading from v2.9 to v3.0

## Removed deprecated features

* The `Vich\UploaderBundle\Mapping\Annotation` namespace has been removed. Use
  `Vich\UploaderBundle\Mapping\Attribute` instead (e.g. replace
  `Vich\UploaderBundle\Mapping\Annotation\Uploadable` with
  `Vich\UploaderBundle\Mapping\Attribute\Uploadable`).
* `Vich\UploaderBundle\Mapping\AnnotationInterface` has been removed. Use
  `Vich\UploaderBundle\Mapping\AttributeInterface` instead.
* All `*Annotation()` methods in `AttributeReader` have been removed. Use the
  corresponding `*Attribute()` methods instead (e.g. `getClassAnnotation()` →
  `getClassAttribute()`).

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

### `final` removed from some classes

The `final` modifier has been removed from the following classes to ease testing
and extensibility:

* `Vich\UploaderBundle\Mapping\PropertyMapping`
* `Vich\UploaderBundle\Mapping\PropertyMappingFactory`
* `Vich\UploaderBundle\Metadata\MetadataReader`
* `Vich\UploaderBundle\Metadata\Driver\AttributeReader`
