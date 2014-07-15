Create a custom file namer
==========================

To create a custom file namer, simply implement the `Vich\UploaderBundle\Naming\NamerInterface`
and in the `name` method of your class return the desired file name. Since your entity
is passed to the `name` method, as well as the mapping describing it, you are
free to get any information from it to create the name, or inject any other
services you require.

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
            namer:              my.namer.product
```

Where `my.namer.product` is the configured id of the service.


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](Resources/doc/index.md)
