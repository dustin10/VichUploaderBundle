VichUploaderBundle
==================

The VichUploaderBundle is a Symfony2 bundle that attempts to ease file
uploads that are attached to an entity. The bundle will automatically name and
save the uploaded file according to the configuration specified on a per-property
basis using a mix of configuration and annotations. After the entity has been created
and the file has been saved, if configured to do so an instance of
`Symfony\Component\HttpFoundation\File\File` will be loaded into the annotated property
when the entity is loaded from the datastore. The bundle also provides templating helpers
for generating URLs to the file. The file can also be configured to be removed from the
file system upon removal of the entity.

The bundle provides different ways to interact with the filesystem; you can choose
your preferred one by configuration. Basically, you can choose to work with the local
filesystem or integrate gaufrette to have nice abstraction over the filesystem (for more
info see [FileSystemStorage VS GaufretteStorage](#filesystemstorage-vs-gaufrettestorage)).

You can also implement your own StorageInterface if you need to.

## Installation

### Get the bundle

To install the bundle, place it in the `vendor/bundles/Vich/UploaderBundle`
directory of your project. You can do this by adding the bundle to your deps file,
as a submodule, cloning it, or simply downloading the source.

Add the following lines in your composer.json:

```
{
    "require": {
        "vich/uploader-bundle": "dev-master",
        "knplabs/knp-gaufrette-bundle" : "dev-master"
    }
}
```

OR add to `deps` file:

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

### Add the namespace to your autoloader

Next you should add the `Vich` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Knp\Bundle'                => __DIR__.'/../vendor/bundles',
    'Gaufrette'                 => __DIR__.'/../vendor/gaufrette/src',
    'Vich' => __DIR__.'/../vendor/bundles'
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
        new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
        new Vich\UploaderBundle\VichUploaderBundle(),
    );
)
```

**Note:**

> Even if you don't plan to use Gaufrette you must include it in your
> deps file and activate KnpGaufretteBundle

## Usage

VichUploaderBundle tries to handle file uploads according to a combination
of configuration parameters and annotations. In order to have your upload
working you have to:

* Choose which storage service you want to use (vich_uploader.storage.file_system
or vich_uploader.storage.gaufrette)
* Define a basic configuration set
* Annotate your Entities
* Optionally implement namer services (see Namer later)

*Please note that some bundle components have a slightly different meaning according to the
storage service you are using. Read more about it in [Configuration reference](#a-hrefreferenceaconfiguration-reference)*

### FileSystemStorage VS GaufretteStorage

Gaufrette is a great piece of code and provide a great level of filesystem
abstraction. Using Gaufrette, you will be able to store files locally, or using
some external service without impact on your application. This means that you
will be able to change the location of your files by changing configuration,
rather than code.

**For this reason GaufretteStorage if probably the most flexible solution and your
best choice as storage service.**

If you don't need this level of abstraction, if you prefer to
keep things simple, or if you just don't feel comfortable working
with gaufrette you can go with FileSystemStorage.

For more information on how use one storage instead of another,
go to [Configuration](#configuration)  section

### Configuration

First configure the `db_driver` option. You must specify either `orm` or
`mongodb`.

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb
```

And then add your mappings information. In order to map
configuration options to the property of the entity you first
need to create a mapping in the bundle configuration. You
create these mappings
under the `mappings` key. Each mapping should have a unique name.
So, if you wanted
to name your mapping `product_image`, the configuration for this
mapping would be
similar to:

``` yaml
vich_uploader:
    db_driver: orm
    mappings:
        product_image:
            uri_prefix: /images/products
            upload_destination: %kernel.root_dir%/../web/images/products
```

The `upload_destination` is the only required configuration option for an entity mapping.

All options are listed below:

- `upload_destination`: The gaufrette fs id to upload the file to
- `namer`: The id of the file namer service for this entity (See [Namers](#namers) section below)
- `directory_namer`: The id of the directory namer service for this entity (See Namers section below)
- `delete_on_remove`: Set to true if the file should be deleted from the
filesystem when the entity is removed
- `delete_on_update`: Set to true if the file should be deleted from the
filesystem when the file is replaced by an other one
- `inject_on_load`: Set to true if the file should be injected into the uploadable
field property when it is loaded from the data store. The object will be an instance
of `Symfony\Component\HttpFoundation\File\File`

**Note:**

> This is the easiest configuration and will use the default
> storage service (vich_uploader.storage.file_system).
> If you want to use Gaufrette you will have to add some bit
> of configuration (see [gaufrette configuration](#gaufrette-configuration) for more help).

**Note:**

> A verbose configuration reference including all configuration options and their
> default values is included at the bottom of this document.

#### Gaufrette configuration

In order to use Gaufrette you have to configure it. Here is
a sample configuration that stores your file in your local filesystem,
but you can use your preferred adapters and FS (for details
on this topic you should refer to the gaufrette documentation).

``` yaml
knp_gaufrette:
    adapters:
        product_adapter:
            local:
                directory: %kernel.root_dir%/../web/images/products

    filesystems:
        product_image_fs:
            adapter:    product_adapter

vich_uploader:
    db_driver: orm
    gaufrette: true
    storage: vich_uploader.storage.gaufrette
    mappings:
        product_image:
            uri_prefix: /images/products
            upload_destination: product_image_fs
```

Using vich_uploader.storage.gaufrette as the storage service
you can still use the same mappings options that you would
use with default storage.

**Note:**

> In this case upload_destination refer to a gaufrette filesystem
> and directory_namer should be used to generate a valid
> filesystem ID (and not a real path). See more about this
> in [Namers section](#namers)

### Annotate Entities

In order for your entity or document to work with the bundle, you need to add a
few annotations to it. First, annotate your class with the `Uploadable` annotation.
This lets the bundle know that it should look for files to upload in your class when
it is saved, inject the files when it is loaded and check to see if it needs to
remove files when it is removed. Next, you should annotate the fields which hold
the instance of `Symfony\Component\HttpFoundation\File\UploadedFile` when the form
is submitted with the `UploadableField` annotation. The `UploadableField` annotation
has a few required options. They are as follows:

- `mapping`: The mapping specified in the bundle configuration to use
- `fileNameProperty`: The property of the class that will be filled with the file name
generated by the bundle

Lets look at an example using a fictional `Product` ORM entity:

``` php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    // ..... other fields

    /**
     * @Assert\File(
     *     maxSize="1M",
     *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg"}
     * )
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName")
     *
     * @var File $image
     */
    protected $image;

    /**
     * @ORM\Column(type="string", length=255, name="image_name")
     *
     * @var string $imageName
     */
    protected $imageName;
}
```

## Namers

The bundle uses namers to name the files and directories it saves to the filesystem. A namer
implements the `Vich\UploaderBundle\Naming\NamerInterface` interface. If no namer is
configured for a mapping, the bundle will simply use the name of the file that
was uploaded. If you would like to change this, you can use one of the provided namers or implement a custom one.

### File Namer

#### Provided file namer

At the moment there are two available namers:

- vich_uploader.namer_uniqid
- vich_uploader.namer_origname

**vich_uploader.namer_uniqid** will rename your uploaded files using a uniqueid for the name and
keep the extension. Using this namer, foo.jpg will be uploaded as something like 50eb3db039715.jpg.

**vich_uploader.namer_origname** will rename your uploaded files using a uniqueid as the prefix of the
filename and keeping the original name and extension. Using this namer, foo.jpg will be uploaded as
something like 50eb3db039715_foo.jpg

To use it, you just have to specify the service id for the `namer` configuration option of your mapping :

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image_fs
            namer: vich_uploader.namer_uniqid
```

#### Create a custom file namer

To create a custom file namer, simply implement the `Vich\UploaderBundle\Naming\NamerInterface`
and in the `name` method of your class return the desired file name. Since your entity
is passed to the `name` method, as well as the field name, you are free to get any information
from it to create the name, or inject any other services that you require.

**Note**:

> The name returned should include the file extension as well. This can easily
> be retrieved from the `UploadedFile` instance using the `getExtension` or `guessExtension`
> depending on what version of PHP you are running.

After you have created your namer and configured it as a service, you simply specify
the service id for the `namer` configuration option of your mapping. An example:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            namer: my.namer.product
```

Here `my.namer.product` is the configured id of the service.

If no namer is configured for a mapping, the bundle will simply use the name of the file that
was uploaded.

### Directory Namer

To create a custom directory namer, simply implement the
`Vich\UploaderBundle\Naming\DirectoryNamerInterface`
and in the `directoryName` method of your class return the absolute directory.
Since your entity, field name
and default `upload_destination` are all passed to the `directoryName` method
you are free to get any information
from it to create the name, or inject any other services that you require.

After you have created your directory namer and configured it as a service, you simply specify
the service id for the `directory_namer` configuration option of your mapping. An example:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            directory_namer: my.directory_namer.product
```

If no directory namer is configured for a mapping, the bundle will simply use the `upload_destination` configuration option.

**Note**:

> If you are using Gaufrette to abstract from the filesystem the name returned
> will be used as a gaufrette filesystem ID and not as a proper path.

## Generating URLs

To get a url for the file you can use the `vich_uploader.templating.helper`
service as follows:

``` php
$entity = // get the entity..
$helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
$path = $helper->asset($entity, 'image');
```
or in a Twig template you can use the `vich_uploader_asset` function:

``` twig
<img src="{{ vich_uploader_asset(product, 'image') }}" alt="{{ product.name }}" />
```

You must specify the annotated property you wish to get the file path for.

**Note:**

> The path returned is relative to the web directory which is specified
> using the `uri_prefix` configuration parameter.

## Configuration Reference

Below is the full default configuration for the bundle:

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb
    twig: true
    gaufrette: false # set to true to enable gaufrette support
    storage: vich_uploader.storage.file_system
    mappings:
        product_image:
            uri_prefix: web # uri prefix to resource
            upload_destination: ~ # gaufrette storage fs id, required
            namer: ~ # specify a file namer service id for this entity, null default
            directory_namer: ~ # specify a directory namer service id for this entity, null default
            delete_on_remove: true # determines whether to delete file upon removal of entity
            inject_on_load: true # determines whether to inject a File instance upon load
        # ... more mappings
```

- `storage`: The id of the storage service used by the bundle to
store files. The bundle ships with vich_uploader.storage.file_system
and vich_uploader.storage.gaufrette see
[FileSystemStorage VS GaufretteStorage](#filesystemstorage-vs-gaufrettestorage)
