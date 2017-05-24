Serving files with a controller
===============================

There are a few use-cases for which serving uploaded files through a controller
instead directly serving them from the HTTP server can be useful. It could be
used to **check that the user has the rights** to download the file, to **serve the
file under a different name** that the one saved in the database, to **force the
download of the file** instead of opening it in the same browser tab, …

For all these reasons, the bundle provides a [`DownloadHandler`](https://github.com/dustin10/VichUploaderBundle/blob/master/Handler/DownloadHandler.php)
that can be used in your controllers.

### Usage

This handler is exposed in the container as a service named `vich_uploader.download_handler`.
The entry point will be the `downloadObject` method, taking an object and the
name of the property containing the file, and returning a `Response` allowing to
download the file.

```php
class AcmeController extends Controller
{
    public function downloadImageAction(Image $image)
    {
        $downloadHandler = $this->get('vich_uploader.download_handler');

        return $downloadHandler->downloadObject($image, $fileField = 'imageFile');
    }
}
```

### Renaming files

This handler can also be used to rename the downloaded files.

```php
class AcmeController extends Controller
{
    public function downloadImageAction(Image $image)
    {
        $downloadHandler = $this->get('vich_uploader.download_handler');
        $fileName   = 'foo.png';

        return $downloadHandler->downloadObject($image, $fileField = 'imageFile', $objectClass = null, $fileName);
    }
}
```

By setting the `$fileName` variable to *foo.png*, I ensure that no matter
the original filename of the file, it will be downloaded as *foo.png*.

You can pass `true` as `$fileName` and in this case file will be served with original file name.

Using this feature, using a *unique id namer* to store the file and restore
their original name only when they are downloaded is possible (as long as you
also store their original name in the database).

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
