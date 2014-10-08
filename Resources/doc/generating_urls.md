Generating URLs
===============

## Generating a URL in a Controller

To get a URL for the file, you can use the `vich_uploader.templating.helper`
service as follows:

``` php
$entity = // get the entity..
$helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
$path = $helper->asset($entity, 'product_image');
```

Where `product_image` is the mapping name used in your entity where you added the
`UploadableField` annotation/configuration.

**Note:**

> The path returned is relative to the web directory which is specified
> using the `uri_prefix` configuration parameter.


## Generating a URL in a Twig Template

In a Twig template you can use the `vich_uploader_asset` function:

``` twig
<img src="{{ vich_uploader_asset(product, 'product_image') }}" alt="{{ product.name }}" />
```

**Note:**

> If the `product` variable is hydrated as an array (instead of an object), you
> will need to manually specify the class name:

```html+jinja
{{ vich_uploader_asset(product, 'image', 'FooBundle\Entity\Product') }}
```


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](index.md)
