# Namers

The bundle uses namers to name the files and directories it saves to the filesystem. A namer
implements the `Vich\UploaderBundle\Naming\NamerInterface` interface.
You must use one of the provided namers or implement a custom one.

## File Namer

### Provided file namers

At the moment there are several available namers:

* `Vich\UploaderBundle\Naming\UniqidNamer`
* `Vich\UploaderBundle\Naming\OrignameNamer`
* `Vich\UploaderBundle\Naming\PropertyNamer`
* `Vich\UploaderBundle\Naming\HashNamer`
* `Vich\UploaderBundle\Naming\Base64Namer`
* `Vich\UploaderBundle\Naming\SmartUniqueNamer`
* `Vich\UploaderBundle\Naming\SlugNamer`

**UniqidNamer** will rename your uploaded files using a uniqueid for the name and
keep the extension. Using this namer, "foo.jpg" will be uploaded as something like "0eb3db03971550eb3b0371.jpg".

**OrignameNamer** will rename your uploaded files using a uniqueid as the prefix of the
filename and keeping the original name and extension. Using this namer, "foo.jpg" will be uploaded as
something like "50eb3db039715_foo.jpg"

**PropertyNamer** will use a property or a method to name the file. You have to specify which
property will be used:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products_fs
            namer:
                service: Vich\UploaderBundle\Naming\PropertyNamer
                options: { property: 'slug' } # supposing that the object contains a "slug" property or a "getSlug" method
```

**HashNamer** will use a hash of random string to name the file. You also can specify
hash `algorithm` and result `length` of the file:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products_fs
            namer:
                service: Vich\UploaderBundle\Naming\HashNamer
                options: { algorithm: 'sha256', length: 50 }
```

**Base64Namer** will generate a URL-safe base64 decodable random string to name the file.
You can specify the `length` of the random string. Using this namer, "foo.jpg" will be uploaded as something
like "6FMNgvkdUs.jpg"

**SmartUniqueNamer** will rename your uploaded files appending a strong uniqueid to the original name, while
applying a transliteration. Using this namer, "a Strange name.jpg" will be uploaded as something like
"a-strange-name-0eb3db03971550eb3b0371.jpg".

**SlugNamer** will only transliterate uploaded file. Then, it will search if such name already exists and, if so,
will append a progressive number (to ensure uniqueness). This is useful when you want to keep your names as closer
as possible to original ones, but is also limited to simple situations (i.e. when you're using a single mapped entity).
To use it, you just have to specify the service for the `namer` configuration option of your mapping:

``` yaml
# config/services.yaml
services:
    Vich\UploaderBundle\Naming\SlugNamer:
        arguments:
            $service: '@App\Repository\MyFileRepository'
            $method: findOneByPath
```

### Configuration

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products_fs
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer # or any other namer listed above
```

### Extension Handling

By default, namers use smart extension logic that preserves specific file extensions (like `.csv`, `.gpx`, `.xlsb`)
even when the MIME type suggests a different extension.  
You can control this behavior with the `namer_keep_extension` option:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products_fs
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            namer_keep_extension: true  # Always keep the original file extension
```

**Important**: The `namer_keep_extension` option only works with namers that implement `ConfigurableInterface`.
If you try to use this option with a custom namer that doesn't implement this interface, you'll get an exception
with instructions on how to fix it.

#### Behavior Examples

With `namer_keep_extension: false` (default):

* `document.csv` → `unique-name-123.csv` (preserved because csv is in the safe list)
* `document.xyz` → `unique-name-123.txt` (changed to guessed extension)

With `namer_keep_extension: true`:

* `document.csv` → `unique-name-123.csv` (preserved)
* `document.xyz` → `unique-name-123.xyz` (preserved)

#### Security Considerations

> [!WARNING]
> When using `namer_keep_extension: true`, always validate file extensions to prevent security risks.
> Files with potentially dangerous extensions (like `.php`, `.exe`, `.sh`, `.bat`) could pose security threats
> if served directly by the web server.

Consider using additional security measures such as:

* Storing uploaded files outside the web root
* Serving files through a controller that validates permissions
* Using Content-Security-Policy headers to prevent script execution

### How-to

* [Create a custom file namer](file_namer/howto/create_a_custom_file_namer.md)

## Directory Namer

Like file namers, directory namers allow you to customize the directory in which
uploaded files will be stored.

> [!NOTE]
> Directory namers are called when a file is uploaded but also later, when you
> want to retrieve the path or URL of an already uploaded file. That's why
> **directory namers MUST be stateless** and rely only on the data provided by
> the mapping or the entity itself to determine the directory.

### Provided directory namers

At the moment there are several available namers:

* `Vich\UploaderBundle\Naming\SubdirDirectoryNamer`
* `Vich\UploaderBundle\Naming\PropertyDirectoryNamer`
* `Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer`
* `Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer`
* `Vich\UploaderBundle\Naming\ChainDirectoryNamer`

**SubdirDirectoryNamer** creates subdirs depending on the file name, i.e. `abcdef.jpg` will be
stored in a folder `ab`. It is also possible to configure how many chars use per directory name and
how many directories to create.

To use it, you just have to specify the service for the `directory_namer`
configuration option of your mapping:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products
            directory_namer: Vich\UploaderBundle\Naming\SubdirDirectoryNamer
```

Or provide configuration:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products
            directory_namer:
                service: Vich\UploaderBundle\Naming\SubdirDirectoryNamer
                options: { chars_per_dir: 1, dirs: 2 } # will create directory "a/b" for "abcdef.jpg"
```

**PropertyDirectoryNamer** will use a property or a method to name the directory.

To use it, you just have to specify the service for the `directory_namer`
configuration option of your mapping, and **must** set a property,
optionally you can use the `transliterate` option to remove special chars from directory name:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products
            directory_namer:
                service: vich_uploader.namer_directory_property
                options: { property: 'slug', transliterate: true } # supposing that the object contains a "slug" property or a "getSlug" method
```

**CurrentDateTimeDirectoryNamer** creates subdirs depending on the current locale datetime. By default, it will be
created in the `Y/m/d` format. It is possible to configure the datetime format used to create directories.
For details of datetime formats see <http://php.net/manual/en/function.date.php>.
You should also pass to this namer an option declaring a property where uploading datetime is stored in your object.
Such property will be accessed via PropertyAccessor, so it can be a public property or a getter method.
For example, if your object has a `getUploadTimestamp(): \DateTimeInterface` method, you can pass
`date_time_property: uploadTimestamp` to namer.

To use it, you just have to specify the service for the `directory_namer`
configuration option of your mapping:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products
            directory_namer:
                service: Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer
                options:
                    date_time_format: 'Y/d/m' # will create directory "2018/23/09" for current date "2018-09-23"
                    date_time_property: uploadTimestamp # see above example
```

**ConfigurableDirectoryNamer** creates subdirs which path is given in the directory namer's options.

To use it, you just have to specify the service for the `directory_namer`
configuration option of your mapping, and **must** set the option `directory_path`:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products
            directory_namer:
                service: vich_uploader.namer_directory_configurable
                options:
                    directory_path: 'folder/subfolder/subsubfolder'
```

**ChainDirectoryNamer** allows you to chain multiple directory namers together, concatenating their
results with a configurable separator. This is useful when you need to combine multiple naming
strategies, for example organizing files by date and then by a property value.

To use it, specify the service for the `directory_namer` configuration option and configure
the `namers` option with a list of directory namers to chain:

``` yaml
vich_uploader:
    # ...
    mappings:
        products:
            upload_destination: products
            directory_namer:
                service: Vich\UploaderBundle\Naming\ChainDirectoryNamer
                options:
                    namers:
                        - service: Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer
                          options:
                              date_time_format: 'Y/m'
                              date_time_property: createdAt
                        - service: Vich\UploaderBundle\Naming\PropertyDirectoryNamer
                          options:
                              property: category.slug
                    separator: '/'  # optional, defaults to '/'
```

This configuration will create directories like `2024/01/electronics` for a product in the
"electronics" category uploaded in January 2024.

> [!NOTE]
> Empty directory names returned by any namer in the chain are automatically filtered out.
> For example, if one namer returns an empty string, it won't add an extra separator to the path.

If no directory namer is configured for a mapping, the bundle will simply use
the `upload_destination` configuration option.

### How-tos

* [Writing a custom directory namer](directory_namer/howto/create_a_custom_directory_namer.md)

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](index.md)
