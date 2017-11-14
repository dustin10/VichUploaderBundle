Namers
======

The bundle uses namers to name the files and directories it saves to the filesystem. A namer
implements the `Vich\UploaderBundle\Naming\NamerInterface` interface. If no namer is
configured for a mapping, the bundle will simply use the name of the file that
was uploaded. If you would like to change this, you can use one of the provided namers or implement a custom one.

## File Namer

### Provided file namers

At the moment there are several available namers:

  * `vich_uploader.namer_uniqid`
  * `vich_uploader.namer_origname`
  * `vich_uploader.namer_property`
  * `vich_uploader.namer_hash`
  * `vich_uploader.namer_base64`

**vich_uploader.namer_uniqid** will rename your uploaded files using a uniqueid for the name and
keep the extension. Using this namer, foo.jpg will be uploaded as something like 50eb3db039715.jpg.

**vich_uploader.namer_origname** will rename your uploaded files using a uniqueid as the prefix of the
filename and keeping the original name and extension. Using this namer, foo.jpg will be uploaded as
something like 50eb3db039715_foo.jpg

**vich_uploader.namer_property** will use a property or a method to name the
file.

**vich_uploader.namer_hash** will use a hash of random string to name the file. You also can specify
hash `algorithm` and result `length` of the file

**vich_uploader.namer_base64** will generate a URL-safe base64 decodable random string to name the file.
You can specify the `length` of the random string. Using this namer, foo.jpg will be uploaded as something
like 6FMNgvkdUs.jpg

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

**N.B:** when using the `namer_property` namer, you have to specify which
property will be used.

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image_fs
            namer:
                service: vich_uploader.namer_property
                options: { property: 'slug'} # supposing that the object contains a "slug" attribute or a "getSlug" method
```


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

### Provided directory namers

At the moment there are several available namers:

  * `vich_uploader.directory_namer_subdir`
  * `vich_uploader.namer_directory_property`

**vich_uploader.directory_namer_subdir** creates subdirs depends of file name, `abcdef.jpg` will be 
stored in as folder `ab`. It is also possible configure how many chars use per directory name and 
how many directories to create. 

To use it, you just have to specify the service id for the `directory_namer`
configuration option of your mapping:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            directory_namer:    vich_uploader.directory_namer_subdir
```

Or provide configuration:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            directory_namer:    
                service: vich_uploader.directory_namer_subdir
                options: {chars_per_dir: 1, dirs: 2} # will create directory "a/b" for "abcdef.jpg"
```

**vich_uploader.namer_directory_property** will use a property or a method to name the directory. 

To use it, you just have to specify the service id for the `directory_namer`
configuration option of your mapping, and **must** set a property,
optionally you can use the `transliterate` option to remove special char from directory name:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            directory_namer:
                service: vich_uploader.namer_directory_property
                options: { property: 'slug', transliterate: true} # supposing that the object contains a "slug" attribute or a "getSlug" method
```

If no directory namer is configured for a mapping, the bundle will simply use
the `upload_destination` configuration option.

### How-to

  * [Writing a custom directory namer](directory_namer/howto/create_a_custom_directory_namer.md)


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](index.md)
