Installation
============

## Get the bundle using composer

Add VichUploaderBundle by running this command from the terminal at the root of
your Symfony project:

```bash
composer require vich/uploader-bundle
```

Alternatively, you can add the requirement `"vich/uploader-bundle": "^1.6"` to your composer.json and run `composer update`.
This could be useful when the installation of VichUploaderBundle is not compatible with some currently installed dependencies. Anyway, the previous option is the preferred way, since composer can pick the best requirement constraint for you.

## Enable the bundle

To start using the bundle, register the bundle in your application's kernel class:

```php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Vich\UploaderBundle\VichUploaderBundle(),
            // ...
        ];
    }
}
```


## Choose and configure a persistence engine

Four engines are currently supported:

  * [orm](http://www.doctrine-project.org/projects/orm.html)
  * [mongodb](http://doctrine-mongodb-odm.readthedocs.org/en/latest/)
  * [phpcr](http://doctrine-phpcr-odm.readthedocs.org/en/latest/)
  * [propel](http://propelorm.org/Propel/).

Once the chosen persistence engine is installed and configured, tell
VichUploaderBundle that you want to use it.

```yaml
# app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb or propel or phpcr
```

**Note:**

> Propel requires a bit more in order to work with this bundle. Check [Propel's
> section](propel.md) to know what to configure.


## That was it!

Yea, the bundle is installed! Move onto the [usage section](usage.md) to find out how
to configure and setup your first upload.
