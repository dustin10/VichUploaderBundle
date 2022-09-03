# VichImageType Field

The bundle provides a custom form type in order to ease the upload, deletion and
download of images.

In order to use it, just define your field as a `VichImageType` as shown in the
following example:

```php
// ...
use Vich\UploaderBundle\Form\Type\VichImageType;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ...

        $builder->add('imageFile', VichImageType::class, [
            'required' => false,
            'allow_delete' => true,
            'delete_label' => '...',
            'download_label' => '...',
            'download_uri' => true,
            'image_uri' => true,
            'imagine_pattern' => '...',
            'asset_helper' => true,
        ]);
    }
}
```

## allow_delete

**type**: `bool` **default**: `true`

## delete_label

**type**: `string` **default**: `vich_uploader.form_label.delete_confirm`

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'delete_label' => 'Remove Image',
]);
```

## asset_helper

**type**: `bool` **default**: `false`

If set to `true`, download uri will be generated with `asset()` method from `symfony/asset` component.

## download_uri

**type**: `bool`, `string`, `callable` **default**: `true`

If set to `true`, download uri will automatically resolved using storage.

Can be string

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'download_uri' => $router->generateUrl('acme_download_image', $product->getId()),
]);
```

Can be a callable

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'download_uri' => static function (Product $product) use ($router) {
        return $router->generateUrl('acme_download_image', $product->getId());
    },
]);
```

## download_label

**type**: `bool`, `string`, `callable`, `Symfony\Component\PropertyAccess\PropertyPath` **default**: `'download'`

If set to `true`, download label will use original file name.

Can be string

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'download_label' => 'download_file',
]);
```

Can be callable

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'download_label' => static fn (Product $product): string => $product->getTitle(),
]);
```

Can be property path

```php
use Symfony\Component\PropertyAccess\PropertyPath;
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'download_label' => new PropertyPath('title'),
]);
```

## image_uri

**type**: `bool`, `string`, `callable` **default**: `true`

If set to `true`, download uri will automatically resolved using storage.

Can be string

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'image_uri' => 'full uri for image',
]);

```

Can be callable

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('genericFile', VichImageType::class, [
    'image_uri' => static function (Photo $photo, $resolvedUri) use ($cacheManager) {
        // $cacheManager is LiipImagine cache manager
        return $cacheManager->getBrowserPath(
            $resolvedUri,
            'photo_thumb',
            ['thumbnail' => ['size' => [$photo->getWidth(), $photo->getHeigth()]]]
        );
    },
]);
```

## imagine_pattern

**type**: `string` **default**: `null`

If set, image will automatically transformed using [LiipImagineBundle](https://github.com/liip/LiipImagineBundle/).

Example

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('photo', VichImageType::class, [
    'imagine_pattern' => 'product_photo_320x240',
]);
```

## storage_resolve_method

**type**: `int` **default**: `0`

Allowed values:

```php
VichImageType::STORAGE_RESOLVE_URI = 0;
VichImageType::STORAGE_RESOLVE_PATH_ABSOLUTE = 1;
VichImageType::STORAGE_RESOLVE_PATH_RELATIVE = 2;
```

Example

```php
use Vich\UploaderBundle\Form\Type\VichImageType;

$builder->add('photo', VichImageType::class, [
    'storage_resolve_method' => VichImageType::STORAGE_RESOLVE_PATH_RELATIVE,
]);
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
