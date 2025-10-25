# FileRequired Validator

The `FileRequired` constraint extends Symfony's `NotBlank` to provide validation for file upload fields.
It validates that either an existing file is present or a new file has been uploaded.

## Basic Usage

```php
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Vich\UploaderBundle\Validator\Constraints as VichAssert;

#[Vich\Uploadable]
class Document
{
    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'file.name')]
    #[VichAssert\FileRequired(target: 'file')]
    private ?File $fileUpload = null;

    private ?EmbeddedFile $file = null;
}
```

## How It Works

The validator checks these conditions in order:

1. If the target property contains a file with a name, validation passes
2. If the upload field contains a valid `UploadedFile`, validation passes
3. If the upload field contains a `ReplacingFile`, validation passes
4. Otherwise, it falls back to standard `NotBlank` validation

## Configuration

### Target Property

The `target` parameter specifies which property contains the existing file:

```php
#[VichAssert\FileRequired(target: 'file')]        // Points to $this->file
#[VichAssert\FileRequired(target: 'image')]       // Points to $this->image
#[VichAssert\FileRequired(target: 'attachment')]  // Points to $this->attachment
```

### Standard Options

Since `FileRequired` extends `NotBlank`, it supports all standard options:

```php
#[VichAssert\FileRequired(
    target: 'file',
    message: 'Please upload a file.',
    groups: ['upload']
)]
```

## Form Integration

```php
use Vich\UploaderBundle\Form\Type\VichFileType;

$builder->add('fileUpload', VichFileType::class, [
    'required' => false,  // Let the validator handle this
    'allow_delete' => true,
]);
```

## Dependencies

Requires the Symfony Validator component:

```bash
composer require symfony/validator
```

The validator is automatically registered with Symfony's dependency injection.
