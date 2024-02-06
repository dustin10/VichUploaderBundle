# Installation

## Get the bundle using composer

Add VichUploaderBundle by running this command from the terminal at the root of
your Symfony project:

```bash
composer require vich/uploader-bundle
```

Alternatively, you can add the requirement `"vich/uploader-bundle": "^2.2"` to your composer.json and run
`composer update`. This could be useful when the installation of VichUploaderBundle is not compatible with some
currently installed dependencies. Anyway, the previous option is the preferred way, since composer can pick the
best requirement constraint for you.

## Enable the bundle

If you use Flex (you should!), the bundle is automatically enabled with a recipe and no further action is required.
Otherwise, to start using the bundle, register it in your application's kernel class:

```php
// app/AppKernel.php (your kernel class may be defined in a different class/path)
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

Three engines are currently supported:

* [orm](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/index.html)
* [mongodb](https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/1.2/index.html)
* [phpcr](https://www.doctrine-project.org/projects/doctrine-phpcr-odm/en/latest/index.html)

Once the chosen persistence engine is installed and configured, tell
VichUploaderBundle that you want to use it.

```yaml
# config/packages/vich_uploader.yaml or app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb or phpcr
```

## That was it!

Yeah, the bundle is installed! Move onto the [usage section](usage.md) to find out how
to configure and set up your first upload.
