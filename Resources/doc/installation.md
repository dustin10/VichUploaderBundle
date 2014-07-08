Installation
============

## Installation notes

### Get the bundle using composer

Add VichUploaderBundle by running the command:

```bash
$ php composer.phar require vich/uploader-bundle 'dev-master'
```

**Note:**

> Because VichUploaderBundle supports Doctrine and Propel, you also will have
> to install the dependencies required by the persistence provider you want to
> use.
> Don't worry, they are detailed in the Doctrine or Propel sections below.
> The same goes for storage providers.


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

Just make sure that `doctrine/orm`, `doctrine/mongodb-odm` or `doctrine/phpcr-odm`
is installed and properly registered in your application.


## Propel

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
