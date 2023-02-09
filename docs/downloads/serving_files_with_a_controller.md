# Serving files with a controller

There are a few use-cases for which serving uploaded files through a controller
instead directly serving them from the HTTP server can be useful. It could be
used to **check that the user has the rights** to download the file, to **serve the
file under a different name** that the one saved in the database, to **force the
download of the file** instead of opening it in the same browser tab, …

For all these reasons, the bundle provides a [`DownloadHandler`](https://github.com/dustin10/VichUploaderBundle/blob/master/src/Handler/DownloadHandler.php)
that can be used in your controllers.

## Usage

This handler is exposed in the container as a service named `Vich\UploaderBundle\Handler\DownloadHandler`.
The entry point will be the `downloadObject` method, taking an object and the
name of the property containing the file, and returning a `Response` allowing to
download the file.

```php
class AcmeController extends AbstractController
{
    public function downloadImageAction(Image $image, DownloadHandler $downloadHandler): Response
    {
        return $downloadHandler->downloadObject($image, $fileField = 'imageFile');
    }
}
```

## Displaying inline

Instead of forcing the file to be downloaded by you browser, you can set it as an **inline** content.
Depending on the capabilities of the browser, the file should be displayed inside the browser.
You can use it to keep the file non publicly accessible (with access checks for exemple) but still
displayable inside HTML (for images) or directly previewable (like PDFs).

Set the `forceDownload` argument to `false` to disable the forced download behaviour.

```php
class AcmeController extends AbstractController
{
    public function downloadImageAction(Image $image, DownloadHandler $downloadHandler): Response
    {
        return $downloadHandler->downloadObject($image, $fileField = 'imageFile', $objectClass = null, $fileName = null, $forceDownload = false);
    }
}
```

## Renaming files

This handler can also be used to rename the downloaded files.

```php
final class AcmeController extends AbstractController
{
    public function downloadImageAction(Image $image, DownloadHandler $downloadHandler): Response
    {
        $fileName = 'foo.png';

        return $downloadHandler->downloadObject($image, 'imageFile', null, $fileName);
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
