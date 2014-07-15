XML mappings
============

You can choose to describe your entities in XML. This bundle supports this
format and comes with the following syntax to declare your uploadable fields:

```xml
# src/Acme/DemoBundle/Resources/config/vich_uploader/Product.xml
<vich_uploader class="Acme\DemoBundle\Entity\Product">
  <field mapping="product_image" name="image" filename_property="image_name" />
</vich_uploader>
```

To be found, the mapping configuration MUST be in the `Resources/config/vich_uploader`
directory of the bundle containing the entity you want to describe.

**N.B:**

> In order to be able to use this format, make sure that the `simplexml`
> extension is enabled (it is by default).


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
