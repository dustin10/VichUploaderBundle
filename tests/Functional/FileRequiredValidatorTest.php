<?php

namespace Vich\UploaderBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;
use Vich\UploaderBundle\Tests\Validator\TestFileUploadEntity;

final class FileRequiredValidatorTest extends WebTestCase
{
    public function testValidationFailsWhenNoFileIsProvided(): void
    {
        $client = self::createClient();
        $container = $client->getContainer();

        if (!$container->has('validator')) {
            self::markTestSkipped('Validator service not available in test environment');
        }

        $validator = $container->get('validator');

        // Simulate what happens in a controller when form is submitted without file
        $entity = new TestFileUploadEntity();
        $entity->image = null; // No existing file
        $entity->imageFile = null; // No uploaded file

        $violations = $validator->validate($entity);

        // Should have validation errors
        self::assertGreaterThan(0, $violations->count(), 'Should have validation errors when no file is provided');

        // Check that we have a FileRequired violation
        $fileRequiredViolations = [];
        foreach ($violations as $violation) {
            if ('imageFile' === $violation->getPropertyPath()) {
                $fileRequiredViolations[] = $violation;
            }
        }

        self::assertGreaterThan(0, \count($fileRequiredViolations), 'Should have FileRequired validation error');
        self::assertStringContainsString('should not be blank', $fileRequiredViolations[0]->getMessage());
    }

    public function testValidationPassesWhenValidFileIsUploaded(): void
    {
        $client = self::createClient();
        $container = $client->getContainer();

        if (!$container->has('validator')) {
            self::markTestSkipped('Validator service not available in test environment');
        }

        $validator = $container->get('validator');

        // Simulate what happens in a controller when valid file is uploaded
        $entity = new TestFileUploadEntity();
        $entity->image = null; // No existing file

        // Create a real UploadedFile (simulating form upload)
        $uploadedFile = new UploadedFile(__FILE__, 'test.php', 'text/plain', null, true);
        $entity->imageFile = $uploadedFile;

        $violations = $validator->validate($entity);

        // Filter out only FileRequired violations for imageFile
        $fileRequiredViolations = [];
        foreach ($violations as $violation) {
            if ('imageFile' === $violation->getPropertyPath()
                && \str_contains($violation->getMessage(), 'should not be blank')) {
                $fileRequiredViolations[] = $violation;
            }
        }

        self::assertCount(0, $fileRequiredViolations, 'FileRequired validation should pass when file is uploaded');
    }

    public function testValidationPassesWhenFileExistsButNoNewUpload(): void
    {
        $client = self::createClient();
        $container = $client->getContainer();

        if (!$container->has('validator')) {
            self::markTestSkipped('Validator service not available in test environment');
        }

        $validator = $container->get('validator');

        // Simulate edit scenario: existing file present, no new upload
        $entity = new TestFileUploadEntity();
        $existingFile = new File();
        $existingFile->setName('existing-image.jpg');
        $entity->image = $existingFile; // Existing file
        $entity->imageFile = null; // No new upload

        $violations = $validator->validate($entity);

        // Filter out only FileRequired violations for imageFile
        $fileRequiredViolations = [];
        foreach ($violations as $violation) {
            if ('imageFile' === $violation->getPropertyPath()
                && \str_contains($violation->getMessage(), 'should not be blank')) {
                $fileRequiredViolations[] = $violation;
            }
        }

        self::assertCount(0, $fileRequiredViolations, 'FileRequired validation should pass when existing file is present');
    }

    public function testValidationWithReplacingFileSimulatesFileReplacement(): void
    {
        $client = self::createClient();
        $container = $client->getContainer();

        if (!$container->has('validator')) {
            self::markTestSkipped('Validator service not available in test environment');
        }

        $validator = $container->get('validator');

        // Simulate programmatic file replacement (e.g., from command, migration)
        $entity = new TestFileUploadEntity();
        $entity->image = null; // No existing file

        // Use ReplacingFile (common in CLI operations, data imports)
        $replacingFile = new ReplacingFile(__FILE__);
        $entity->imageFile = $replacingFile;

        $violations = $validator->validate($entity);

        // Filter out only FileRequired violations for imageFile
        $fileRequiredViolations = [];
        foreach ($violations as $violation) {
            if ('imageFile' === $violation->getPropertyPath()
                && \str_contains($violation->getMessage(), 'should not be blank')) {
                $fileRequiredViolations[] = $violation;
            }
        }

        self::assertCount(0, $fileRequiredViolations, 'FileRequired validation should pass with ReplacingFile');
    }

    public function testValidationBehaviorMatchesRealWorldScenarios(): void
    {
        $client = self::createClient();
        $container = $client->getContainer();

        if (!$container->has('validator')) {
            self::markTestSkipped('Validator service not available in test environment');
        }

        $validator = $container->get('validator');

        // Test typical form submission scenarios that would happen in real controllers

        // Scenario 1: Create form submitted without file
        $createEntity = new TestFileUploadEntity();
        $createViolations = $validator->validate($createEntity);
        self::assertGreaterThan(0, $createViolations->count(), 'Create form should require file');

        // Scenario 2: Edit form submitted without new file but with existing file
        $editEntity = new TestFileUploadEntity();
        $existingFile = new File();
        $existingFile->setName('profile.jpg');
        $editEntity->image = $existingFile;
        $editViolations = $validator->validate($editEntity);

        $editFileRequiredViolations = [];
        foreach ($editViolations as $violation) {
            if ('imageFile' === $violation->getPropertyPath()
                && \str_contains($violation->getMessage(), 'should not be blank')) {
                $editFileRequiredViolations[] = $violation;
            }
        }
        self::assertCount(0, $editFileRequiredViolations, 'Edit form should allow existing file');

        // Scenario 3: API upload with programmatic file
        $apiEntity = new TestFileUploadEntity();
        $apiEntity->imageFile = new ReplacingFile(__FILE__);
        $apiViolations = $validator->validate($apiEntity);

        $apiFileRequiredViolations = [];
        foreach ($apiViolations as $violation) {
            if ('imageFile' === $violation->getPropertyPath()
                && \str_contains($violation->getMessage(), 'should not be blank')) {
                $apiFileRequiredViolations[] = $violation;
            }
        }
        self::assertCount(0, $apiFileRequiredViolations, 'API upload should work with ReplacingFile');
    }

    public function testFormSubmissionWithValidatorTestController(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client); // Create database schema

        // First get the form to get the token
        $crawler = $client->request('GET', '/validator-test/upload');
        self::assertResponseIsSuccessful();

        // Extract form and submit without file
        $form = $crawler->selectButton('Save')->form();
        $form['validated_image[title]'] = 'Test Image';

        $client->submit($form);

        // Should not redirect (form has errors)
        self::assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        self::assertStringContainsString('should not be blank', $content, 'Should display FileRequired validation error');

        // Test upload form submission with valid file - should succeed
        $crawler = $client->request('GET', '/validator-test/upload');
        $form = $crawler->selectButton('Save')->form();
        $form['validated_image[title]'] = 'Test Image with File';

        $uploadedFile = new UploadedFile(__FILE__, 'test-upload.php', 'text/plain', null, true);

        $client->submit($form, [
            'validated_image' => [
                'imageFile' => ['file' => $uploadedFile],
            ],
        ]);

        // Should redirect to view page (successful upload)
        self::assertResponseRedirects();
        $location = $client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/validator-test/view/', $location, 'Should redirect to view page');
    }
}
