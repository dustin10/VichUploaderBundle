VichFileType
============

The bundle provides a custom form type in order to ease the upload, deletion and
download of files.

In order to use it, just define your field as a `vich_file` as show in the
following example:

```php
class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('image', 'vich_file', array(
            'required'      => false,
            'allow_delete'  => true, // not mandatory, default is true
            'download_link' => true, // not mandatory, default is true
        ));
    }
}
```

**Note for Symfony3 users:**
In case you are using Symfony3, you must use the `VichFileType::class` to specify the field type:

```php
// ...
use Vich\UploaderBundle\Form\Type\VichFileType;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('image', VichFileType::class, array(
            'required'      => false,
            'allow_delete'  => true, // not mandatory, default is true
            'download_link' => true, // not mandatory, default is true
        ));
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

> **N.B:** the `form_themes` parameter is introduced in Symfony 2.5, check 
[the documentation](http://symfony.com/doc/2.3/cookbook/form/form_customization.html#php) if you use an older version.

See [Symfony's documentation on form themes](http://symfony.com/doc/current/cookbook/form/form_customization.html#form-theming)
for more information.

## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
