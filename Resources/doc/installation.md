Installation
============

## Installation notes

### Get the bundle

Regardless of the persistence provider you want to use, you can install this
bundle with Composer, with a `deps.lock` file or a git submodule.

If you want to use Composer, just add the right dependencies to your
`composer.json`:

``` json
{
    "require": {
        "vich/uploader-bundle": "dev-master",
        "knplabs/knp-gaufrette-bundle" : "dev-master"
    }
}
```

Or add to `deps` file:

```
[gaufrette]
    git=http://github.com/KnpLabs/Gaufrette.git
    version=v0.1.3

[KnpGaufretteBundle]
    git=http://github.com/KnpLabs/KnpGaufretteBundle.git
    target=/bundles/Knp/Bundle/GaufretteBundle

[VichUploaderBundle]
    git=git://github.com/dustin10/VichUploaderBundle.git
    target=/bundles/Vich/UploaderBundle
```

Or you may add the bundle as a git submodule:

``` bash
$ git submodule add https://github.com/dustin10/VichUploaderBundle.git vendor/bundles/Vich/UploaderBundle
```

**Note:**

> Because VichUploaderBundle supports Doctrine and Propel, you also will have
> to install the dependencies required by the persistence provider you want to
> use.
> Don't worry, they are detailed in the Doctrine or Propel sections below.


### Add the namespace to your autoloader

If you don't use Composer, you will have to update your autoloader's
configuration:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Knp\Bundle'  => __DIR__.'/../vendor/bundles',
    'Gaufrette'   => __DIR__.'/../vendor/gaufrette/src',
    'Vich'        => __DIR__.'/../vendor/bundles'
));
```

### Initialize the bundle

To start using the bundle, register the bundle in your application's kernel class:

``` php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(), // or new Oneup\FlysystemBundle\OneupFlysystemBundle(),

        new Vich\UploaderBundle\VichUploaderBundle(),
        // ...
    );
)
```

**Note:**

> Both KnpGaufretteBundle and OneupFlysystemBundle are supported but none of
> them is required. Require and activate one of them only if you want to
> abstract your file storage with Gaufrette or Flysystem.


## Doctrine

Just make sure that `doctrine/orm` or `doctrine/mongodb-odm` are installed and
properly registered in your application.


## Propel

Two dependencies are required to enable Propel's support:

``` json
{
    "require": {
        "willdurand/propel-eventdispatcher-behavior": ">=1.2",
        "willdurand/propel-eventdispatcher-bundle": ">=1.0",
        "vich/uploader-bundle": "dev-master"
    }
}
```

``` php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(), // or new Oneup\FlysystemBundle\OneupFlysystemBundle(),
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
