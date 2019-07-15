Uploading files without a form
==============================

Sometimes you want to upload a file without using a form, for example if you defined some kind of API.
In such case, you can use the
[`UploadHandler`](https://github.com/dustin10/VichUploaderBundle/blob/master/Handler/UploadHandler.php)
service.

### Usage

This handler is exposed in the container as a service named `Vich\UploaderBundle\Handler\UploadHandler`.
The entry point will be the `upload` method, taking an uploadable object and the
name of the property containing the file.

```php
class AcmeController extends AbstractController
{
    public function uploadImage(Image $image, UploadHandler $uploadHandler, Request $request): Response
    {
        $entity = new MyEntity();
        $entity->setFile($request->files->get('myImage'))
        $uploadHandler->upload($image, 'imageFile');

        return $this->json('OK');
    }
}
```
## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
