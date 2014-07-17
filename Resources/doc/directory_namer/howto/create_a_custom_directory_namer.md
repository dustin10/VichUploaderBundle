Create a custom directory namer
===============================

To create a custom directory namer, simply implement
`Vich\UploaderBundle\Naming\DirectoryNamerInterface`
and in the `directoryName` method of your class return the directory.

Since your entity and the mapping information describing it are both passed to
the `directoryName` method you are free to get any information from it to
create the name, or inject any other services you require.

After you have created your directory namer and configured it as a service, you simply specify
the service id for the `directory_namer` configuration option of your mapping. An example:

``` yaml
vich_uploader:
    # ...
    mappings:
        product_image:
            upload_destination: product_image
            directory_namer:    my.directory_namer.product
```


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](Resources/doc/index.md)
