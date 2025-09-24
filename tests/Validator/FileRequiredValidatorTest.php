<?php

namespace Vich\UploaderBundle\Tests\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Validator\Constraints\FileRequired;
use Vich\UploaderBundle\Validator\Constraints\FileRequiredValidator;

final class FileRequiredValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): FileRequiredValidator
    {
        return new FileRequiredValidator();
    }

    #[DataProvider('getValidValues')]
    public function testValidValues(mixed $value, ?object $entity = null): void
    {
        $constraint = new FileRequired(target: 'image');

        if ($entity) {
            $this->setObject($entity);
        }

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public static function getValidValues(): \Generator
    {
        // Test with an existing file
        $entityWithFile = new TestFileUploadEntity();
        $file = new File();
        $file->setName('existing-file.jpg');
        $entityWithFile->image = $file;
        yield 'null value with existing file' => [null, $entityWithFile];

        // Test with a valid uploaded file
        $entityWithUpload = new TestFileUploadEntity();
        $uploadedFile = new UploadedFile(__FILE__, 'test.txt', 'text/plain', null, true);
        yield 'valid uploaded file' => [$uploadedFile, $entityWithUpload];

        // Test with replacing file
        $entityWithReplace = new TestFileUploadEntity();
        $replacingFile = new ReplacingFile(__FILE__);
        yield 'replacing file' => [$replacingFile, $entityWithReplace];
    }

    public function testInvalidUploadedFileDoesNotSkipValidation(): void
    {
        $entity = new TestFileUploadEntity();
        $entity->image = null;

        // Create an invalid uploaded file
        $uploadedFile = new UploadedFile(__FILE__, 'test.txt', 'text/plain', \UPLOAD_ERR_PARTIAL);

        $constraint = new FileRequired(target: 'image');
        $this->setObject($entity);

        $this->validator->validate($uploadedFile, $constraint);

        // Since the uploaded file is not null but validation conditions aren't met,
        // it falls through to NotBlank validation which passes (non-null object)
        $this->assertNoViolation();
    }

    public function testValidateWithoutTargetFallsBackToNotBlank(): void
    {
        $constraint = new FileRequired(); // No target specified

        $this->validator->validate(null, $constraint);

        // Should fall back to NotBlank behavior for null values
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'null')
            ->setCode('c1051bb4-d103-4f74-8988-acbcafc7fdc3')
            ->assertRaised();
    }

    public function testValidateWithNonNullValueWithoutTarget(): void
    {
        $constraint = new FileRequired(); // No target specified

        $this->validator->validate('some value', $constraint);

        // Non-null value should pass NotBlank validation
        $this->assertNoViolation();
    }

    public function testValidateWithNullFileProperty(): void
    {
        $entity = new TestFileUploadEntity();
        $entity->image = null; // Null file property

        $constraint = new FileRequired(target: 'image');
        $this->setObject($entity);

        $this->validator->validate(null, $constraint);

        // Should fail validation
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'null')
            ->setCode('c1051bb4-d103-4f74-8988-acbcafc7fdc3')
            ->assertRaised();
    }

    public function testValidateWithEmptyFileName(): void
    {
        $entity = new TestFileUploadEntity();
        $file = new File();
        $file->setName(''); // Empty name
        $entity->image = $file;

        $constraint = new FileRequired(target: 'image');
        $this->setObject($entity);

        $this->validator->validate(null, $constraint);

        // Should fail validation
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'null')
            ->setCode('c1051bb4-d103-4f74-8988-acbcafc7fdc3')
            ->assertRaised();
    }

    public function testCustomMessage(): void
    {
        $message = 'Custom file required message';
        $constraint = new FileRequired(message: $message, target: 'image');
        $entity = new TestFileUploadEntity();

        $this->setObject($entity);

        $this->validator->validate(null, $constraint);

        $this->buildViolation($message)
            ->setParameter('{{ value }}', 'null')
            ->setCode('c1051bb4-d103-4f74-8988-acbcafc7fdc3')
            ->assertRaised();
    }
}
