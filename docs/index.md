# Getting started with VichUploaderBundle

VichUploaderBundle provides an easy way to link file uploads with a persistence
layer.

So if you want to save file uploads to ORM entities, MongoDB ODM documents,
or PHPCR ODM documents, you are in the right place.

## Installation

Don't worry, it will be quick and easy (I promise!):

* [Installation procedure](installation.md)

## Usage

* [Basic usage](usage.md)
* [Generating URLs](generating_urls.md)

## Cookbooks

### Namers-related

* [Working with file and directory namers](namers.md)
* [Writing a custom file namer](file_namer/howto/create_a_custom_file_namer.md)
* [Writing a custom directory namer](directory_namer/howto/create_a_custom_directory_namer.md)

### Storage-related

* [Using Gaufrette as storage abstraction](storage/gaufrette.md)
* [Using Flysystem as storage abstraction](storage/flysystem.md)
* [Using a custom storage](storage/custom.md)

### Mapping-related

* [YAML](mapping/yaml.md)
* [XML](mapping/xml.md)

### Forms-related

> The Symfony Form component allows you to easily create, process and reuse HTML forms.

To install the package, run the following command:

```bash
composer require symfony/form
```

* [Using the bundled file form type](form/vich_file_type.md)
* [Using the bundled image form type](form/vich_image_type.md)

### Other usages

* [Inject files from other sources](other_usages/replacing_file.md)

### Download-related

* [Serving files with a controller](downloads/serving_files_with_a_controller.md)

### Event-related

* [Events](events/events.md)
* [Remove files asynchronously](events/howto/remove_files_asynchronously.md)

## Useful resources

* [Configuration reference](configuration_reference.md)
* [Console commands](commands.md)
* [Known issues](known_issues.md)
* [Symfony support policy](symfony_support_policy.md)
