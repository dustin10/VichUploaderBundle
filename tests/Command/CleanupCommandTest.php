<?php

namespace Vich\UploaderBundle\Tests\Command;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Command\CleanupCommand;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\Stub\TestQueryBuilderInterface;
use Vich\UploaderBundle\Tests\Stub\TestQueryInterface;
use Vich\UploaderBundle\Tests\Stub\TestRepositoryInterface;

final class CleanupCommandTest extends AbstractCommandTestCase
{
    public function testCommandWithNoUploadableClasses(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([]);

        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [],
            []
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--force' => true]);

        self::assertStringContainsString('No uploadable classes found', $output);
    }

    public function testCommandWithInvalidMapping(): void
    {
        $reader = $this->mockMetadataReader();
        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [],
            ['valid_mapping' => []]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--mapping' => 'invalid_mapping', '--force' => true]);

        self::assertStringContainsString('Mapping "invalid_mapping" does not exist', $output);
    }

    public function testDryRunMode(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([]);

        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [],
            []
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--dry-run' => true]);

        self::assertStringContainsString('Running in dry-run mode', $output);
        self::assertStringContainsString('No files will be deleted', $output);
    }

    public function testCommandWithSpecificMapping(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn(['App\\Entity\\Product']);

        // getUploadableFields is called twice: once for processing and once for finding sample field
        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with('App\\Entity\\Product', 'product_image')
            ->willReturn([]);

        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [],
            ['product_image' => ['upload_destination' => '/tmp']]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--mapping' => 'product_image', '--force' => true]);

        self::assertStringContainsString('Processing mapping: product_image', $output);
    }

    public function testCommandWithNoObjectManager(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn(['App\\Entity\\Product']);

        // getUploadableFields is called twice: once for processing and once for finding sample field
        $reader->expects(self::exactly(2))
            ->method('getUploadableFields')
            ->with('App\\Entity\\Product', 'test_mapping')
            ->willReturn([
                'image' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'image',
                    'fileNameProperty' => 'imageName',
                ],
            ]);

        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        // Create an empty manager registry (no managers)
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--force' => true]);

        self::assertStringContainsString('No object manager found for class "App\\Entity\\Product"', $output);
    }

    public function testCommandWithRepositoryWithoutQueryBuilder(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn(['App\\Entity\\Product']);

        // getUploadableFields is called twice: once for processing and once for finding sample field
        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with('App\\Entity\\Product', 'test_mapping')
            ->willReturn([
                'image' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'image',
                    'fileNameProperty' => 'imageName',
                ],
            ]);

        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        // Create a repository without createQueryBuilder method
        $repository = $this->createMock(ObjectRepository::class);

        $metadataFactory = $this->createMock(ClassMetadataFactoryInterface::class);
        $metadataFactory->expects($this->once())
            ->method('isTransient')
            ->with('App\\Entity\\Product')
            ->willReturn(false);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with('App\\Entity\\Product')
            ->willReturn($repository);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$objectManager]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--force' => true]);

        self::assertStringContainsString('support query', $output);
        self::assertStringContainsString('Skipping', $output);
        // After skipping the repository, the command tries to create a sample instance which fails
        self::assertStringContainsString('Could not create instance', $output);
    }

    public function testCommandWithCustomBatchSize(): void
    {
        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([]);

        $storage = self::createStub(StorageInterface::class);
        $mappingFactory = self::createStub(PropertyMappingFactoryInterface::class);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [],
            []
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--batch-size' => '500', '--force' => true]);

        self::assertStringNotContainsString('error', \strtolower($output));
    }

    public function testCommandDetectsOrphanedFiles(): void
    {
        // This test verifies the scenario where storage has MORE files than referenced in database
        // Expected: orphaned files should be reported/deleted

        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([DummyEntity::class]);

        // getUploadableFields is called twice: once for processing and once for finding sample field
        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with(DummyEntity::class, 'test_mapping')
            ->willReturn([
                'file' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        // Create a mock storage that returns 3 files (older than 2 hours to pass min-age filter)
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('listFiles')
            ->willReturn([
                new \Vich\UploaderBundle\Storage\StoredFile('file1.txt', \time() - 7200), // 2 hours old
                new \Vich\UploaderBundle\Storage\StoredFile('file2.txt', \time() - 7200), // 2 hours old
                new \Vich\UploaderBundle\Storage\StoredFile('orphaned.txt', \time() - 7200), // 2 hours old
            ]); // 3 files in storage

        // Create mock mapping
        $mapping = $this->createMock(PropertyMappingInterface::class);
        $mapping->expects($this->atLeastOnce())
            ->method('getMappingName')
            ->willReturn('test_mapping');
        $mapping->expects($this->atLeastOnce())
            ->method('getFileName')
            ->willReturnOnConsecutiveCalls('file1.txt', 'file2.txt'); // Only 2 files referenced
        $mapping
            ->method('getUploadDir')
            ->willReturn('');

        $mappingFactory = $this->createMock(PropertyMappingFactoryInterface::class);
        $mappingFactory
            ->method('fromField')
            ->willReturn($mapping);

        // Create mock entities (2 entities with files)
        $entity1 = new \stdClass();
        $entity2 = new \stdClass();

        // Create mock query builder and repository
        $qb = $this->createQueryBuilderMock([$entity1, $entity2]);
        $repository = $this->createRepositoryMock($qb);

        // Create mock object manager
        $objectManager = $this->createObjectManagerMock($repository, DummyEntity::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$objectManager]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--dry-run' => true]);

        // Verify the command detected orphaned file
        self::assertStringContainsString('Found 2 referenced file(s) in database', $output);
        self::assertStringContainsString('Found 3 file(s) in storage', $output);
        self::assertStringContainsString('Found 1 orphaned file(s)', $output);
    }

    public function testCommandWithNoOrphanedFiles(): void
    {
        // This test verifies the scenario where all files in storage are referenced in database
        // Expected: no orphaned files should be reported

        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([DummyEntity::class]);

        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with(DummyEntity::class, 'test_mapping')
            ->willReturn([
                'file' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        // Storage has exactly the same files as in database (older than 2 hours to pass min-age filter)
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('listFiles')
            ->willReturn([
                new \Vich\UploaderBundle\Storage\StoredFile('file1.txt', \time() - 7200), // 2 hours old
                new \Vich\UploaderBundle\Storage\StoredFile('file2.txt', \time() - 7200), // 2 hours old
            ]); // 2 files in storage

        $mapping = $this->createMock(PropertyMappingInterface::class);
        $mapping->expects($this->atLeastOnce())
            ->method('getMappingName')
            ->willReturn('test_mapping');
        $mapping->expects($this->atLeastOnce())
            ->method('getFileName')
            ->willReturnOnConsecutiveCalls('file1.txt', 'file2.txt'); // 2 files referenced
        $mapping
            ->method('getUploadDir')
            ->willReturn('');

        $mappingFactory = $this->createMock(PropertyMappingFactoryInterface::class);
        $mappingFactory
            ->method('fromField')
            ->willReturn($mapping);

        $entity1 = new \stdClass();
        $entity2 = new \stdClass();

        $qb = $this->createQueryBuilderMock([$entity1, $entity2]);
        $repository = $this->createRepositoryMock($qb);
        $objectManager = $this->createObjectManagerMock($repository, DummyEntity::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$objectManager]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--force' => true]);

        // Verify no orphaned files were found
        self::assertStringContainsString('Found 2 referenced file(s) in database', $output);
        self::assertStringContainsString('Found 2 file(s) in storage', $output);
        self::assertStringContainsString('Found 0 orphaned file(s)', $output);
    }

    public function testCommandWithMissingFiles(): void
    {
        // This test verifies the scenario where database references MORE files than exist in storage
        // Expected: command should not fail, just report fewer files in storage

        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([DummyEntity::class]);

        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with(DummyEntity::class, 'test_mapping')
            ->willReturn([
                'file' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        // Storage has FEWER files than referenced in the database (older than 2 hours to pass min-age filter)
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('listFiles')
            ->willReturn([
                new \Vich\UploaderBundle\Storage\StoredFile('file1.txt', \time() - 7200), // 2 hours old
            ]); // Only 1 file in storage

        $mapping = $this->createMock(PropertyMappingInterface::class);
        $mapping->expects($this->atLeastOnce())
            ->method('getMappingName')
            ->willReturn('test_mapping');
        $mapping->expects($this->atLeastOnce())
            ->method('getFileName')
            ->willReturnOnConsecutiveCalls('file1.txt', 'file2.txt', 'file3.txt'); // 3 files referenced
        $mapping
            ->method('getUploadDir')
            ->willReturn('');

        $mappingFactory = $this->createMock(PropertyMappingFactoryInterface::class);
        $mappingFactory
            ->method('fromField')
            ->willReturn($mapping);

        $entity1 = new \stdClass();
        $entity2 = new \stdClass();
        $entity3 = new \stdClass();

        $qb = $this->createQueryBuilderMock([$entity1, $entity2, $entity3]);
        $repository = $this->createRepositoryMock($qb);
        $objectManager = $this->createObjectManagerMock($repository, DummyEntity::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$objectManager]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        $output = $this->executeCommand('vich:cleanup', $command, ['--force' => true]);

        // Verify the command handled missing files gracefully
        self::assertStringContainsString('Found 3 referenced file(s) in database', $output);
        self::assertStringContainsString('Found 1 file(s) in storage', $output);
        self::assertStringContainsString('Found 0 orphaned file(s)', $output);
    }

    public function testCommandDeletesOrphanedFilesInRealMode(): void
    {
        // This test verifies the complete happy path: orphaned file is detected AND deleted
        // Expected: storage->remove() is called with correct parameters

        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([DummyEntity::class]);

        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with(DummyEntity::class, 'test_mapping')
            ->willReturn([
                'file' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        // Storage returns 3 files (2 hours old to pass min-age filter)
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('listFiles')
            ->willReturn([
                new \Vich\UploaderBundle\Storage\StoredFile('file1.txt', \time() - 7200),
                new \Vich\UploaderBundle\Storage\StoredFile('file2.txt', \time() - 7200),
                new \Vich\UploaderBundle\Storage\StoredFile('orphaned.txt', \time() - 7200),
            ]);

        // KEY ASSERTION: Verify remove() is called ONCE with correct parameters for orphaned file
        $storage->expects($this->once())
            ->method('remove')
            ->with(
                self::anything(), // object (temporary instance created by command)
                self::callback(static function ($mapping) {
                    // Verify mapping is correct
                    return $mapping instanceof PropertyMappingInterface
                        && 'test_mapping' === $mapping->getMappingName();
                }),
                '' // directory (empty string for root, as returned by getUploadDir)
            );

        // Mock mapping that returns 2 files (file1.txt, file2.txt) → orphaned.txt is orphan
        $mapping = $this->createMock(PropertyMappingInterface::class);
        $mapping->expects($this->atLeastOnce())
            ->method('getMappingName')
            ->willReturn('test_mapping');
        $mapping->expects($this->atLeastOnce())
            ->method('getFileName')
            ->willReturnOnConsecutiveCalls('file1.txt', 'file2.txt'); // Only 2 files referenced
        $mapping
            ->method('getUploadDir')
            ->willReturn('');
        $mapping
            ->method('setFileName'); // Called when creating temp object for deletion

        $mappingFactory = $this->createMock(PropertyMappingFactoryInterface::class);
        $mappingFactory
            ->method('fromField')
            ->willReturn($mapping);

        // Mock 2 entities with files
        $entity1 = new \stdClass();
        $entity2 = new \stdClass();

        $qb = $this->createQueryBuilderMock([$entity1, $entity2]);
        $repository = $this->createRepositoryMock($qb);
        $objectManager = $this->createObjectManagerMock($repository, DummyEntity::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$objectManager]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        // Execute WITHOUT --dry-run (so remove() actually gets called)
        $output = $this->executeCommand('vich:cleanup', $command, ['--force' => true]);

        // Verify output shows the correct flow
        self::assertStringContainsString('Found 2 referenced file(s) in database', $output);
        self::assertStringContainsString('Found 3 file(s) in storage', $output);
        self::assertStringContainsString('Found 1 orphaned file(s)', $output);
        self::assertStringContainsString('1 orphaned file(s) deleted', $output); // "deleted" not "found"
        self::assertStringNotContainsString('dry-run', \strtolower($output));
    }

    public function testMinAgeSkipsRecentFiles(): void
    {
        // This test verifies that --min-age properly skips files newer than the cutoff
        // and only counts/deletes files that are old enough.

        $reader = $this->mockMetadataReader();
        $reader->expects($this->once())
            ->method('getUploadableClasses')
            ->willReturn([DummyEntity::class]);

        // getUploadableFields is called twice: once for processing and once for finding the sample field
        $reader->expects($this->exactly(2))
            ->method('getUploadableFields')
            ->with(DummyEntity::class, 'test_mapping')
            ->willReturn([
                'file' => [
                    'mapping' => 'test_mapping',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                ],
            ]);

        // Storage returns 2 files: one recent (1 minute old) and one old (2 hours old)
        $recent = new \Vich\UploaderBundle\Storage\StoredFile('recent.txt', \time() - 60);
        $old = new \Vich\UploaderBundle\Storage\StoredFile('old.txt', \time() - 7200);

        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('listFiles')
            ->willReturn([$recent, $old]);
        // In dry-run, no deletion should occur
        $storage->expects($this->never())
            ->method('remove');

        // Mapping: we don't care about DB-referenced files here because we return 0 entities below
        $mapping = $this->createMock(PropertyMappingInterface::class);
        $mapping
            ->method('getMappingName')
            ->willReturn('test_mapping');
        $mapping
            ->method('getUploadDir')
            ->willReturn('');

        $mappingFactory = $this->createMock(PropertyMappingFactoryInterface::class);
        $mappingFactory
            ->method('fromField')
            ->willReturn($mapping);

        // Doctrine mocks: repository returns 0 entities so there are no DB references
        $qb = $this->createQueryBuilderMock([]);
        $repository = $this->createRepositoryMock($qb);
        $objectManager = $this->createObjectManagerMock($repository, DummyEntity::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagers')
            ->willReturn([$objectManager]);

        $command = new CleanupCommand(
            $storage,
            $mappingFactory,
            $reader,
            [$managerRegistry],
            ['test_mapping' => ['upload_destination' => '/tmp']]
        );

        // Set --min-age to 30 minutes so the recent file (1 minute old) is skipped
        $output = $this->executeCommand('vich:cleanup', $command, ['--mapping' => 'test_mapping', '--min-age' => '30', '--dry-run' => true]);

        // Verify output indicates the recent file was skipped and only the old file considered
        self::assertStringContainsString('Skipped 1 file(s) younger than cutoff age', $output);
        self::assertStringContainsString('Found 1 file(s) in storage (matching age criteria)', $output);
        self::assertStringContainsString('Found 1 orphaned file(s)', $output);
        // And since dry-run, wording should use "found" and not "deleted"
        self::assertStringContainsString('1 orphaned file(s) found', $output);
    }

    /**
     * Helper method to create a mock query builder.
     */
    private function createQueryBuilderMock(array $entities): MockObject
    {
        $query = $this->createMock(TestQueryInterface::class);
        $query
            ->method('getSingleScalarResult')
            ->willReturn(\count($entities));
        $query
            ->method('getResult')
            ->willReturn($entities);

        $qb = $this->createMock(TestQueryBuilderInterface::class);
        $qb
            ->method('select')
            ->willReturnSelf();
        $qb
            ->method('setFirstResult')
            ->willReturnSelf();
        $qb
            ->method('setMaxResults')
            ->willReturnSelf();
        $qb
            ->method('getQuery')
            ->willReturn($query);

        return $qb;
    }

    /**
     * Helper method to create a mock repository.
     */
    private function createRepositoryMock(MockObject $queryBuilder): MockObject
    {
        $repository = $this->createMock(TestRepositoryInterface::class);
        $repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $repository
            ->method('getClassName')
            ->willReturn(DummyEntity::class);

        return $repository;
    }

    /**
     * Helper method to create a mock object manager.
     */
    private function createObjectManagerMock(MockObject $repository, string $className): MockObject
    {
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('isTransient')
            ->with($className)
            ->willReturn(false);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with($className)
            ->willReturn($repository);
        $objectManager
            ->method('clear');

        return $objectManager;
    }
}
