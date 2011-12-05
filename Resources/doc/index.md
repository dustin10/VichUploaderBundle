VichUploaderBundle
==================

The VichUploaderBundle is a simple Symfony2 bundle that attempts to ease file 
uploads that are attached to an entity. The bundle will automatically name and 
save the uploaded file according to the configuration specified on a per-entity 
basis. The bundle also provides templating helpers for generating URLs to the 
file as well. The file can also be configured to be removed from the file system 
upon removal of the entity.

## Installation

### Get the bundle

To install the bundle, place it in the `vendor/bundles/Vich/UploaderBundle` 
directory of your project. You can do this by adding the bundle to your deps file, 
as a submodule, cloning it, or simply downloading the source.

Add to `deps` file:

```
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
        new Vich\UploaderBundle\VichUploaderBundle(),
    );
)
```

### Configuration

First configure the `db_driver` option. You must specify either `orm` or 
`mongodb`.

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb
```

In order to map options to you use the fully qualified class name of the entity 
under the `mappings` key. So, if your entity was `Acme\DemoBundle\Entity\Product`, 
the configuration for this entity would be as follows:

``` yaml
vich_uploader:
    # ...
    mappings:
        Acme\DemoBundle\Entity\Product:
            upload_dir: %kernel.base_dir%/../web/images/products
```

The `upload_dir` is the only required configuration option for an entity mapping. 
All options are listed below:

- `upload_dir`: The directory to upload the file to
- `namer`: The id of the namer service for this entity (See Namers section below)
- `delete_on_remove`: Set to true if the file should be deleted from the 
filesystem when the entity is removed.

**Note:**

```
A verbose configuration reference including all configuration options and their 
default values is included at the bottom of this document.
```

## Implement the UploadableInterface

In order for your entity to work with the bundle it must implement the 
`Vich\UploaderBundle\Model\UploadableInterface`. The interface has three methods: 

- `getFile`: This method must return the `Symfony\Component\HttpFoundation\File\UploadedFile` 
instance that is created by the `file` form field type
- `getFileName`: Gets the file name
- `setFileName`: Sets the file name

## Namers

The bundle uses namers to name the files it saves to the filesystem. Each namer 
implements the `Vich\UploaderBundle\Naming\NamerInterface` interface. There are 
two namers included with the bundle:

- `vich_uploader.namer.default`: The default namer, it simply uses the same name 
as the uploaded file
- `vich_uploader.namer.md5`: This namer uses `md5(time())` as the name of the file

To create a custom namer, simply implement the `NamerInterface` and return a string 
in the `name` method. Since your entity is passed to the `name` method you are free 
to get any information from it to create the name, or inject any other services 
that you require.

After you have created your namer and configured it as a service, you simply specify 
the service id for the `namer` configuration option. If you set the `namer` option 
at the top-level of the configuration then that namer will be used as the default 
namer. If you specify the `namer` configuration option under the `mappings` section 
for your entity then the namer will be used only for that entity.

## Generating URLs

To get a url for the file you can use the `vich_uploader.uploader` service as 
follows:

``` php
$entity = // get the entity..
$uploader = $this->container->get('vich_uploader.uploader');
$path = $uploader->getPublicPath($entity);
```
or in a Twig template you can use the `vich_uploader_asset` function:

``` twig
<img src="{{ vich_uploader_asset(product) }}" alt="{{ product.name }}" />
```
## Limitations

- Currently the bundle only supports generating a relative url for the file.
- Currently the bundle only supports saving/deleting files to the local filesystem.
- Currently the bundle only supports having one file attached to an entity.

I will only change this when I need to do it for a project I work on. So far, this 
bundle has satisfied all of my needs, but feel free to fork and make a PR.

## Configuration Reference

Below is the default coniguration for the bundle:

``` yaml
# app/config/config.yml
vich_uploader:
    db_driver: orm # or mongodb
    namer: vich_uploader.namer.default
    web_dir_name: web
    twig: true
    mappings:
        Acme\DemoBundle\Entity\Product:
            upload_dir: ~ # required
            namer: ~ # specify a namer service id for this entity
            delete_on_remove: true
        # ...
```