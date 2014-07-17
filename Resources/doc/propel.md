Propel
======

Propel's support is built on BazingaPropelEventDispatcherBundle. This behavior
integrates an event dispatcher into your models, allowing us to integrate
ourselves in their lifecycle and do our work.

We basically just need you to add the `event_dispatcher` behavior to your
"uploadable" models.

## Dependencies

Two additional dependencies are required to enable Propel's support:

``` json
{
    "require": {
        "willdurand/propel-eventdispatcher-behavior": ">=1.2",
        "willdurand/propel-eventdispatcher-bundle": ">=1.0",
        "vich/uploader-bundle": "dev-master"
    }
}
```

## Setup

Register BazingaPropelEventDispatcherBundle after VichUploaderBundle:

``` php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Vich\UploaderBundle\VichUploaderBundle(),
        new Bazinga\Bundle\PropelEventDispatcherBundle\BazingaPropelEventDispatcherBundle(),
        // ..
    );
)
```

**Note:**

> The order between VichUploaderBundle and BazingaPropelEventDispatcherBundle is
> important.

**Note:**

> Each uploadable entity must have the `event_dispatcher` behavior.
> To do this, add the following line in the concerned `schema.xml` files:
> ```<behavior name="event_dispatcher" />```

**Note:**

> Propel2 is **NOT** supported.


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](index.md)
