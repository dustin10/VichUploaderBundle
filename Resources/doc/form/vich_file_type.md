VichFileType
============

The bundle provides a custom form type in order to ease the upload, deletion and
download of files.

In order to use it, just define your field as a `vich_file` as show in the
following example:

```php
public function buildForm(FormBuilderInterface $builder, array $options)
{
    // ...

    $builder->add('image', 'vich_file', array(
        'required'      => false,
        'mapping'       => 'image_mapping', // mandatory
        'allow_delete'  => true, // not mandatory, default is true
        'download_link' => true, // not mandatory, default is true
    ));
}
```

**N.B**:

> Don't forget to specify the right mapping name.


For the form type to fully work, you'll also have to use the form theme bundled
with VichUploaderBundle.

```yaml
# app/config/config.yml
twig:
    # ...
    form:
        resources:
            # other form themes
            - 'VichUploaderBundle:Form:fields.html.twig'
```

See [Symfony's documentation on form themes](http://symfony.com/doc/current/cookbook/form/form_customization.html#form-theming)
for more information.


## That was it!

Check out the docs for information on how to use the bundle! [Return to the
index.](../index.md)
