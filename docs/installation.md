Installation
============

## Get the bundle using composer

Add VichUploaderBundle by running this command from the terminal at the root of
your Symfony project:

```bash
composer require vich/uploader-bundle
```

Alternatively, you can add the requirement `"vich/uploader-bundle": "^1.8"` to your composer.json and run `composer update`.
This could be useful when the installation of VichUploaderBundle is not compatible with some currently installed dependencies. Anyway, the previous option is the preferred way, since composer can pick the best requirement constraint for you.

## Enable the bundle

If you use Flex (you should!), the bundle is automatically enabled and no further action is required.
Otherwise, to start using the bundle, register it in your application's kernel class:

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

  * [orm](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/index.html)
  * [mongodb](https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/1.2/index.html)
  * [phpcr](https://www.doctrine-project.org/projects/doctrine-phpcr-odm/en/latest/index.html)
  * [propel](http://propelorm.org/Propel/)

Once the chosen persistence engine is installed and configured, tell
VichUploaderBundle that you want to use it.

```yaml
# config/packages/vich_uploader.yaml or app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb or propel or phpcr
```

**Note:**

> Propel requires a bit more in order to work with this bundle. Check [Propel's
> section](propel.md) to know what to configure.


## That was it!

Yeah, the bundle is installed! Move onto the [usage section](usage.md) to find out how
to configure and setup your first upload.
