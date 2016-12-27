VichFileType
============

The bundle provides a custom form type in order to ease the upload, deletion and
download of files.

In order to use it, just define your field as a `VichFileType`, as shown in the
following example:

```php
// ...
use Vich\UploaderBundle\Form\Type\VichFileType;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('genericFile', VichFileType::class, [
            'required' => false,
            'allow_delete' => true, // not mandatory, default is true
            'download_link' => true, // not mandatory, default is true
        ]);
    }
}
```

**Note for Symfony < 2.8:**
In case you are using a version of Symfony lower than 2.8, you must use the `vich_file` to specify the field type:

```php
class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('genericFile', 'vich_file', [
            'required' => false,
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
