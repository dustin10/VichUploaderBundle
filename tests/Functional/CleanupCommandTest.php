<?php

namespace Vich\UploaderBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\TestBundle\Entity\Image;

/**
 * End-to-end functional test for vich:cleanup command.
 *
 * This test creates real entities with uploaded files, deletes some rows
 * directly from database (bypassing Doctrine), and verifies the cleanup
 * command correctly identifies and removes orphaned files.
 */
final class CleanupCommandTest extends WebTestCase
{
    public function testCleanupCommandRemovesOrphanedFilesInRealScenario(): void
    {
        if (\headers_sent()) {
            self::markTestSkipped('Headers already sent');
        }

        $client = self::createClient();
        $this->loadFixtures($client);

        $container = self::getKernelContainer($client);
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $uploadsDir = $this->getUploadsDir($client);

        // Step 1: Create 4 images with actual uploaded files
        $filenames = ['file1.png', 'file2.png', 'file3.png', 'file4.png'];

        foreach ($filenames as $i => $filename) {
            // Create temporary uploaded file
            $sourceFile = $this->getImagesDir($client).\DIRECTORY_SEPARATOR.'symfony_black_03.png';
            $tempFile = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.$filename;
            \copy($sourceFile, $tempFile);

            $uploadedFile = new UploadedFile(
                $tempFile,
                $filename,
                'image/png',
                null,
                true // Mark as test file
            );

            $image = new Image();
            $image->setTitle('Image '.($i + 1));
            $image->setImageFile($uploadedFile);

            $em->persist($image);
        }

        $em->flush();

        // Verify all 4 files exist in storage
        foreach ($filenames as $filename) {
            self::assertFileExists($uploadsDir.\DIRECTORY_SEPARATOR.$filename, "File $filename should exist after upload");
        }

        // Step 2: Get image IDs before deleting
        $em->clear(); // Clear to ensure we're working with fresh data
        $imageRepo = $em->getRepository(Image::class);
        $allImages = $imageRepo->findAll();
        self::assertCount(4, $allImages, 'Should have 4 images in database');

        $imageIdsToDelete = [$allImages[1]->getId(), $allImages[3]->getId()]; // Delete image 2 and 4
        $orphanedFiles = [$filenames[1], $filenames[3]]; // file2.png and file4.png will be orphaned
        $referencedFiles = [$filenames[0], $filenames[2]]; // file1.png and file3.png remain referenced

        // Step 3: Delete 2 images DIRECTLY from database (bypassing Doctrine events)
        // This simulates a scenario where files are left orphaned (e.g., CASCADE DELETE, manual DB operation)
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $tableName = $em->getClassMetadata(Image::class)->getTableName();

        foreach ($imageIdsToDelete as $id) {
            $connection->executeStatement(
                "DELETE FROM {$platform->quoteIdentifier($tableName)} WHERE id = ?",
                [$id]
            );
        }

        // Clear entity manager to reflect database changes
        $em->clear();

        // Verify only 2 images remain in database
        $remainingImages = $imageRepo->findAll();
        self::assertCount(2, $remainingImages, 'Should have 2 images after deletion');

        // Verify all 4 files still exist in storage (orphaned files not yet cleaned up)
        foreach ($filenames as $filename) {
            self::assertFileExists($uploadsDir.\DIRECTORY_SEPARATOR.$filename, "File $filename should still exist before cleanup");
        }

        // Step 4: Wait 1 second to ensure files are older than any potential min-age filter
        // (The default min-age is 60 minutes, but we'll use --min-age=0 for faster test)
        \sleep(1);

        // Step 5: Run vich:cleanup command with --force and --min-age=0
        $application = new Application($client->getKernel());
        $command = $application->find('vich:cleanup');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            '--force' => true,
            '--min-age' => '0', // Allow immediate cleanup for testing
        ]);

        self::assertSame(0, $exitCode, 'Command should exit successfully');

        $output = $commandTester->getDisplay();

        // Step 6: Verify command output
        self::assertStringContainsString('Found 2 referenced file(s) in database', $output);
        self::assertStringContainsString('Found 4 file(s) in storage', $output);
        self::assertStringContainsString('Found 2 orphaned file(s)', $output);
        self::assertStringContainsString('2 orphaned file(s) deleted', $output);

        // Step 7: Verify orphaned files were deleted
        foreach ($orphanedFiles as $filename) {
            self::assertFileDoesNotExist(
                $uploadsDir.\DIRECTORY_SEPARATOR.$filename,
                "Orphaned file $filename should be deleted"
            );
        }

        // Step 8: Verify referenced files still exist
        foreach ($referencedFiles as $filename) {
            self::assertFileExists(
                $uploadsDir.\DIRECTORY_SEPARATOR.$filename,
                "Referenced file $filename should NOT be deleted"
            );
        }
    }

    public function testCleanupCommandDryRunDoesNotDeleteFiles(): void
    {
        if (\headers_sent()) {
            self::markTestSkipped('Headers already sent');
        }

        $client = self::createClient();

        // Load fixtures to ensure clean state (removes files from previous tests)
        $this->loadFixtures($client);

        $container = self::getKernelContainer($client);
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $uploadsDir = $this->getUploadsDir($client);

        // Clean uploads directory from previous tests
        if (\is_dir($uploadsDir)) {
            foreach (\scandir($uploadsDir) as $file) {
                if ('.' !== $file && '..' !== $file) {
                    @\unlink($uploadsDir.\DIRECTORY_SEPARATOR.$file);
                }
            }
        }

        // Create 2 images
        $filenames = ['dryrun1.png', 'dryrun2.png'];

        foreach ($filenames as $i => $filename) {
            $sourceFile = $this->getImagesDir($client).\DIRECTORY_SEPARATOR.'symfony_black_03.png';
            $tempFile = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.$filename;
            \copy($sourceFile, $tempFile);

            $uploadedFile = new UploadedFile($tempFile, $filename, 'image/png', null, true);

            $image = new Image();
            $image->setTitle('DryRun Image '.($i + 1));
            $image->setImageFile($uploadedFile);

            $em->persist($image);
        }

        $em->flush();

        // Delete 1 image directly from database
        $em->clear();
        $allImages = $em->getRepository(Image::class)->findAll();
        $imageId = $allImages[0]->getId();

        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $tableName = $em->getClassMetadata(Image::class)->getTableName();
        $connection->executeStatement(
            "DELETE FROM {$platform->quoteIdentifier($tableName)} WHERE id = ?",
            [$imageId]
        );

        $em->clear();

        \sleep(1);

        // Run command with --dry-run
        $application = new Application($client->getKernel());
        $command = $application->find('vich:cleanup');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            '--dry-run' => true,
            '--min-age' => '0',
        ]);

        self::assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();

        // Verify dry-run output
        self::assertStringContainsString('Running in dry-run mode', $output);
        self::assertStringContainsString('No files will be deleted', $output);
        self::assertStringContainsString('Found 1 orphaned file(s)', $output);
        self::assertStringContainsString('1 orphaned file(s) found', $output); // "found" not "deleted"

        // Verify files still exist (dry-run should not delete)
        foreach ($filenames as $filename) {
            self::assertFileExists(
                $uploadsDir.\DIRECTORY_SEPARATOR.$filename,
                "File $filename should still exist after dry-run"
            );
        }
    }

    public function testCleanupCommandRespectsMinAgeFilter(): void
    {
        if (\headers_sent()) {
            self::markTestSkipped('Headers already sent');
        }

        $client = self::createClient();

        // Load fixtures to ensure clean state (removes files from previous tests)
        $this->loadFixtures($client);

        $container = self::getKernelContainer($client);
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $uploadsDir = $this->getUploadsDir($client);

        // Clean uploads directory from previous tests
        if (\is_dir($uploadsDir)) {
            foreach (\scandir($uploadsDir) as $file) {
                if ('.' !== $file && '..' !== $file) {
                    @\unlink($uploadsDir.\DIRECTORY_SEPARATOR.$file);
                }
            }
        }

        // Create 1 image
        $filename = 'recent.png';
        $sourceFile = $this->getImagesDir($client).\DIRECTORY_SEPARATOR.'symfony_black_03.png';
        $tempFile = \sys_get_temp_dir().\DIRECTORY_SEPARATOR.$filename;
        \copy($sourceFile, $tempFile);

        $uploadedFile = new UploadedFile($tempFile, $filename, 'image/png', null, true);

        $image = new Image();
        $image->setTitle('Recent Image');
        $image->setImageFile($uploadedFile);

        $em->persist($image);
        $em->flush();

        // Delete image directly
        $em->clear();
        $imageId = $em->getRepository(Image::class)->findAll()[0]->getId();

        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $tableName = $em->getClassMetadata(Image::class)->getTableName();
        $connection->executeStatement(
            "DELETE FROM {$platform->quoteIdentifier($tableName)} WHERE id = ?",
            [$imageId]
        );

        $em->clear();

        // Run command with --min-age=60 (file is too recent, should be skipped)
        $application = new Application($client->getKernel());
        $command = $application->find('vich:cleanup');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            '--force' => true,
            '--min-age' => '60', // 60 minutes - file is too recent
        ]);

        self::assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();

        // Verify file was skipped due to min-age
        self::assertStringContainsString('Skipped 1 file(s) younger than cutoff age', $output);
        self::assertStringContainsString('Found 0 file(s) in storage (matching age criteria)', $output);

        // Verify file still exists (skipped due to age)
        self::assertFileExists(
            $uploadsDir.\DIRECTORY_SEPARATOR.$filename,
            "File should still exist because it's too recent"
        );
    }
}
