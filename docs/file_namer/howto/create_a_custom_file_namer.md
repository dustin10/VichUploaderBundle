# Create a custom file namer

To create a custom file namer, simply implement the `Vich\UploaderBundle\Naming\NamerInterface`
and in the `name` method of your class return the desired file name. Since your entity
is passed to the `name` method, as well as the mapping describing it, you are
free to get any information from it to create the name, or inject any other
service you require.

> [!NOTE]
> The name returned should include the file extension as well. This can easily
> be retrieved from the `UploadedFile` instance using the `getExtension` or `guessExtension`
> depending on what version of PHP you are running.

## Basic Custom Namer

Here's a simple example:

```php
<?php

namespace App\Naming;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

class MyNamer implements NamerInterface
{
    public function name(object|array $object, PropertyMappingInterface $mapping): string
    {
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        return 'custom_' . uniqid() . '.' . $extension;
    }
}
```

## Configurable Custom Namer

If you want your namer to support configuration options (including the `namer_keep_extension` option),
implement the `Vich\UploaderBundle\Naming\ImmutableConfigurableInterface`:

```php
<?php

namespace App\Naming;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Naming\ImmutableConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

class MyConfigurableNamer implements NamerInterface, ImmutableConfigurableInterface
{
    use \Vich\UploaderBundle\Naming\ConfigurableNamerTrait;
    use \Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

    private bool $keepExtension = false;
    private string $prefix = 'file';

    public function configure(array $options): void
    {
        $this->keepExtension = $options['keep_extension'] ?? $this->keepExtension;
        $this->prefix = $options['prefix'] ?? $this->prefix;
    }

    public function name(object|array $object, PropertyMappingInterface $mapping): string
    {
        $file = $mapping->getFile($object);
        $extension = $this->getExtensionWithOption($file, $this->keepExtension);

        $name = $this->prefix . '_' . uniqid();

        return $extension ? $name . '.' . $extension : $name;
    }
}
```

`withOptions()` is the only method the interface declares. The bundle calls it for each mapping,
including mappings without options, to get a separate configured instance. `ConfigurableNamerTrait`
implements it by cloning the service and calling `configure()` on the copy; that method is no
longer required, but stays useful for service-level defaults.

The trait fits this example's scalar properties. Copy mutable configuration objects in
`__clone()`, or implement `withOptions()` yourself — note that a `readonly` property does not make
its contents immutable, and a service that cannot be cloned can build a new instance instead.
Decorators must also copy the inner namer, e.g. `$this->inner->withOptions($options)`. Always
return a new instance, leaving the source and earlier copies untouched: returning `$this` throws
a `LogicException`.

The older `ConfigurableInterface`, declaring `configure(array $options): void`, is deprecated
since 3.1 and will be removed in 4.0. Namers implementing only that interface still get their
options, but the bundle configures the shared service itself, so every mapping using it ends up
with the options of the last one resolved.

With a configurable namer, you can use options in your configuration:

```yaml
vich_uploader:
    mappings:
        products:
            upload_destination: product_image
            namer:
                service: App\Naming\MyConfigurableNamer
                options: { prefix: 'product', keep_extension: true }
            namer_keep_extension: true  # This will be passed automatically as 'keep_extension' option
```

After you have created your namer and configured it as a service, you simply specify
the service for the `namer` configuration option of your mapping. An example:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: product_image
            namer: App\Naming\MyNamer
```

Where `App\Naming\MyNamer` is the configured service class.

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../../index.md)
