# Generating URLs

## Generating a URL in a Controller

To get a URL for the file, you can use the `Vich\UploaderBundle\Templating\Helper\UploaderHelper`
service as follows:

``` php
$entity = …; // get the entity...
// get the UploaderHelper service...
$path = $helper->asset($entity, 'image');
```

Where `image` is the field name used in your entity where you added the
`UploadableField` annotation/configuration.

If `image` is your only mapped field, you can omit it and use simply `$helper->asset($entity)`.

**Note:**

> The path returned is relative to the public directory which is specified
> using the `uri_prefix` configuration parameter.

## Generating a URL in a Twig Template

In a Twig template you can use the `vich_uploader_asset` function:

``` twig
<img src="{{ vich_uploader_asset(product, 'image') }}" alt="{{ product.name }}">
```

Or, in the simpler case of a single mapped field:

``` twig
<img src="{{ vich_uploader_asset(product) }}" alt="{{ product.name }}">

```

**Note:**

> If the `product` variable is hydrated as an array (instead of an object), you
> will need to manually specify the class name:

```html+jinja
{{ vich_uploader_asset(product, 'image', 'FooBundle\\Entity\\Product') }}
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index](index.md).
