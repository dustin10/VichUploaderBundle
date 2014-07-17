Namers
======

The bundle uses namers to name the files and directories it saves to the filesystem. A namer
implements the `Vich\UploaderBundle\Naming\NamerInterface` interface. If no namer is
configured for a mapping, the bundle will simply use the name of the file that
was uploaded. If you would like to change this, you can use one of the provided namers or implement a custom one.

## File Namer

### Provided file namers

At the moment there are two available namers:

  * `vich_uploader.namer_uniqid`
  * `vich_uploader.namer_origname`

**vich_uploader.namer_uniqid** will rename your uploaded files using a uniqueid for the name and
keep the extension. Using this namer, foo.jpg will be uploaded as something like 50eb3db039715.jpg.

**vich_uploader.namer_origname** will rename your uploaded files using a uniqueid as the prefix of the
filename and keeping the original name and extension. Using this namer, foo.jpg will be uploaded as
something like 50eb3db039715_foo.jpg

To use it, you just have to specify the service id for the `namer` configuration option of your mapping:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image_fs
            namer:              vich_uploader.namer_uniqid
```

If no namer is configured for a mapping, the bundle will simply use the name of the file that
was uploaded.

**Warning:** it means that if two files having the same name are uploaded, one
will override the other.


### How-to

  * [Create a custom file namer](file_namer/howto/create_a_custom_file_namer.md)


## Directory Namer

Like file namers, directory namers allow you to customize the directory in which
uploaded files will be stored.

**Note**:

> Directory namers are called when a file is uploaded but also later, when you
> want to retrieve the path or URL of an already uploaded file. That's why
> **directory namers MUST be stateless** and rely only on the data provided by
> the mapping or the entity itself to determine the directory.

To use it, you just have to specify the service id for the `directory_namer`
configuration option of your mapping:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            directory_namer:    my.directory_namer.product
```

If no directory namer is configured for a mapping, the bundle will simply use
the `upload_destination` configuration option.

### How-to

  * [Writing a custom directory namer](directory_namer/howto/create_a_custom_directory_namer.md)


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](index.md)
