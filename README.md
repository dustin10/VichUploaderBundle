VichUploaderBundle
==================

[![Build Status](https://secure.travis-ci.org/dustin10/VichUploaderBundle.png?branch=master)](http://travis-ci.org/dustin10/VichUploaderBundle)	[![Total Downloads](https://poser.pugx.org/vich/uploader-bundle/downloads.png)](https://packagist.org/packages/vich/uploader-bundle) [![Latest Unstable Version](https://poser.pugx.org/vich/uploader-bundle/v/unstable.png)](https://packagist.org/packages/vich/uploader-bundle)

The VichUploaderBundle is a Symfony2 bundle that attempts to ease file
uploads that are attached to ORM entities, MongoDB ODM documents, PHPCR ODM
documents or Propel models.

- Automatically name and save a file to a configured directory
- Inject the file back into the entity or document when it is loaded from the datastore as an
instance of `Symfony\Component\HttpFoundation\File\File`
- Delete the file from the file system upon removal of the entity or document from the datastore
- Templating helpers to generate public URLs to the file

All of this functionality is fully configurable to allow for app-specific customization.

## Documentation

For usage documentation, see:

[Resources/doc/index.md](https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/index.md)

If upgrading from a previous version, also check:

[UPGRADE.md](https://github.com/dustin10/VichUploaderBundle/blob/master/UPGRADE.md)

## Contributing

See the [CONTRIBUTING](https://github.com/dustin10/VichUploaderBundle/blob/master/CONTRIBUTING.md) file.

## License

See the bundled [LICENSE](https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/meta/LICENSE) file.
