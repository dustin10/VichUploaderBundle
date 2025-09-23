# Create a custom file namer

To create a custom file namer, simply implement the `Vich\UploaderBundle\Naming\NamerInterface`
and in the `name` method of your class return the desired file name. Since your entity
is passed to the `name` method, as well as the mapping describing it, you are
free to get any information from it to create the name, or inject any other
service you require.

> [!NOTE]
> The `name` method in the interface accepts only objects, but your namer should accept both
> objects and arrays. The interface method signature will be fixed in the next major version.
> The name returned should include the file extension as well. This can easily
> be retrieved from the `UploadedFile` instance using the `getExtension` or `guessExtension`
> depending on what version of PHP you are running.

## Basic Custom Namer

Here's a simple example:

```php
<?php

namespace App\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class MyNamer implements NamerInterface
{
    public function name(object|array $object, PropertyMapping $mapping): string
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
implement the `Vich\UploaderBundle\Naming\ConfigurableInterface`:

```php
<?php

namespace App\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

class MyConfigurableNamer implements NamerInterface, ConfigurableInterface
{
    use \Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

    private bool $keepExtension = false;
    private string $prefix = 'file';

    public function configure(array $options): void
    {
        $this->keepExtension = $options['keep_extension'] ?? $this->keepExtension;
        $this->prefix = $options['prefix'] ?? $this->prefix;
    }

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $extension = $this->getExtensionWithOption($file, $this->keepExtension);

        $name = $this->prefix . '_' . uniqid();

        return $extension ? $name . '.' . $extension : $name;
    }
}
```

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
index.](/docs/index.md)
