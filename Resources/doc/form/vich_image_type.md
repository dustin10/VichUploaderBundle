VichImageType
=============

The bundle provides a custom form type in order to ease the upload, deletion and
download of images.

In order to use it, just define your field as a `VichImageType` as shown in the
following example:

```php
// ...
use Vich\UploaderBundle\Form\Type\VichImageType;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('imageFile', VichImageType::class, [
            'required' => false,
            'allow_delete' => true, // optional, default is true
            'download_link' => true, // optional, default is true
            'download_uri' => '...', // optional, if not provided - will automatically resolved using storage
        ]);
    }
}
```

For the form type to fully work, you'll also have to use the form theme bundled
with VichUploaderBundle.

```yaml
# app/config/config.yml
twig:
    form_themes:
        # other form themes
        - 'VichUploaderBundle:Form:fields.html.twig'
```

See [Symfony's documentation on form themes](https://symfony.com/doc/current/form/form_customization.html#form-theming)
for more information.

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
