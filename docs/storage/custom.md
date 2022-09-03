# Custom Storage

The bundle supports some built-in storage, but you can also create your own
by implementing the `Vich\UploaderBundle\Storage\StorageInterface` or by
extending the `Vich\UploaderBundle\Storage\AbstractStorage`.

Once you have implemented it, you need to register it in the Symfony
container and provide its service name in the configuration by
prefixing it with `@`:

``` yaml
vich_uploader:
    db_driver: orm
    storage: '@App\Storage\CustomStorage'
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
