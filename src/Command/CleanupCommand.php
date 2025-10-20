<?php

namespace Vich\UploaderBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Storage\StorageInterface;

#[AsCommand(name: 'vich:cleanup', description: 'Remove orphaned files from storage')]
final class CleanupCommand extends Command
{
    public const DEFAULT_MIN_AGE_MINUTES = 60;
    public const DEFAULT_BATCH_SIZE = 1000;
    public const MAX_BATCH_SIZE = 10000;

    /**
     * @param ManagerRegistry[] $managerRegistries
     */
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly PropertyMappingFactory $mappingFactory,
        private readonly MetadataReader $metadataReader,
        private readonly array $managerRegistries,
        private readonly array $mappings
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be deleted without actually deleting')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Batch size for processing entities', self::DEFAULT_BATCH_SIZE)
            ->addOption('mapping', 'm', InputOption::VALUE_REQUIRED, 'Process only specific mapping')
            ->addOption('min-age', null, InputOption::VALUE_REQUIRED, 'Minimum age in minutes for files to be considered orphaned (prevents race conditions)', self::DEFAULT_MIN_AGE_MINUTES)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force deletion without confirmation (required for non-interactive execution)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');
        $requestedMapping = $input->getOption('mapping');
        $minAgeMinutes = (int) $input->getOption('min-age');
        $force = $input->getOption('force');

        // Validate batch size
        if ($batchSize < 1 || $batchSize > self::MAX_BATCH_SIZE) {
            $io->error(\sprintf('Batch size must be between 1 and %d, got: %d', self::MAX_BATCH_SIZE, $batchSize));

            return self::FAILURE;
        }

        // Validate min-age
        if ($minAgeMinutes < 0) {
            $io->error(\sprintf('Minimum age must be non-negative, got: %d minutes', $minAgeMinutes));

            return self::FAILURE;
        }

        // Calculate cutoff timestamp for file age
        $cutoffTimestamp = \time() - ($minAgeMinutes * 60);

        if ($dryRun) {
            $io->note('Running in dry-run mode. No files will be deleted.');
        } elseif (!$force && $input->isInteractive()) {
            $io->warning('This command will permanently delete orphaned files from storage.');
            $io->note(\sprintf('Files must be older than %d minutes to be considered orphaned.', $minAgeMinutes));

            if (!$io->confirm('Do you want to continue?', false)) {
                $io->info('Operation cancelled.');

                return self::SUCCESS;
            }
        } elseif (!$force) {
            $io->error('The --force flag is required for non-interactive deletion. Use --dry-run to preview files that would be deleted.');

            return self::FAILURE;
        }

        // Validate mapping if specified
        if ($requestedMapping && !isset($this->mappings[$requestedMapping])) {
            $io->error(\sprintf('Mapping "%s" does not exist.', $requestedMapping));

            return self::FAILURE;
        }

        // Get all uploadable classes
        $uploadableClasses = $this->metadataReader->getUploadableClasses();

        if (empty($uploadableClasses)) {
            $io->warning('No uploadable classes found.');

            return self::SUCCESS;
        }

        $io->info(\sprintf('Found %d uploadable class(es).', \count($uploadableClasses)));

        // Process each mapping
        $mappingsToProcess = $requestedMapping ? [$requestedMapping] : \array_keys($this->mappings);

        $totalDeleted = 0;

        foreach ($mappingsToProcess as $mappingName) {
            $io->section(\sprintf('Processing mapping: %s', $mappingName));

            $deleted = $this->processMapping($mappingName, $uploadableClasses, $batchSize, $dryRun, $cutoffTimestamp, $input, $io);
            $totalDeleted += $deleted;

            $io->success(\sprintf(
                'Processed mapping "%s": %d orphaned file(s) %s.',
                $mappingName,
                $deleted,
                $dryRun ? 'found' : 'deleted'
            ));
        }

        $io->success(\sprintf(
            'Total: %d orphaned file(s) %s.',
            $totalDeleted,
            $dryRun ? 'found' : 'deleted'
        ));

        return self::SUCCESS;
    }

    private function processMapping(
        string $mappingName,
        array $uploadableClasses,
        int $batchSize,
        bool $dryRun,
        int $cutoffTimestamp,
        InputInterface $input,
        SymfonyStyle $io
    ): int {
        // Collect all file paths referenced in the database
        $referencedFiles = [];

        foreach ($uploadableClasses as $className) {
            $fields = $this->metadataReader->getUploadableFields($className, $mappingName);

            if (empty($fields)) {
                continue;
            }

            $io->text(\sprintf('Scanning entities: %s', $className));

            // Get all objects for this class
            $objectManager = $this->getManagerForClass($className);

            if (null === $objectManager) {
                $io->warning(\sprintf('No object manager found for class "%s". Skipping.', $className));
                continue;
            }

            /** @var class-string $className */
            $repository = $objectManager->getRepository($className);

            // Check if repository has createQueryBuilder method
            if (!\method_exists($repository, 'createQueryBuilder')) {
                $io->warning(\sprintf('Repository for class "%s" does not support query builder. Skipping.', $className));
                continue;
            }

            $qb = $repository->createQueryBuilder('e');

            // Get total count
            $totalCount = (int) (clone $qb)
                ->select('COUNT(e)')
                ->getQuery()
                ->getSingleScalarResult();

            if (0 === $totalCount) {
                $io->text('No entities found.');
                continue;
            }

            // Only show the progress bar in interactive mode
            $progressBar = null;
            if ($input->isInteractive()) {
                $progressBar = new ProgressBar($io, $totalCount);
                $progressBar->setFormat('verbose');
                $progressBar->start();
            } else {
                $io->text(\sprintf('Processing %d entities...', $totalCount));
            }

            // Process in batches
            $offset = 0;

            while ($offset < $totalCount) {
                $entities = $qb
                    ->setFirstResult($offset)
                    ->setMaxResults($batchSize)
                    ->getQuery()
                    ->getResult();

                foreach ($entities as $entity) {
                    foreach ($fields as $fieldName => $field) {
                        \assert(\is_string($fieldName));
                        $mapping = $this->mappingFactory->fromField($entity, $fieldName);

                        if (null === $mapping || $mapping->getMappingName() !== $mappingName) {
                            continue;
                        }

                        $fileName = $mapping->getFileName($entity);

                        if (!empty($fileName)) {
                            // Get the relative path
                            $uploadDir = $mapping->getUploadDir($entity);
                            $relativePath = (\is_string($uploadDir) && '' !== $uploadDir)
                                ? $uploadDir.'/'.$fileName
                                : $fileName;

                            // Normalise path separators
                            $relativePath = \str_replace('\\', '/', $relativePath);

                            $referencedFiles[$relativePath] = true;
                        }
                    }

                    if (null !== $progressBar) {
                        $progressBar->advance();
                    }
                }

                // Clear object manager to free memory
                /* @var \Doctrine\Persistence\ObjectManager $objectManager */
                $objectManager->clear();

                $offset += $batchSize;
            }

            if (null !== $progressBar) {
                $progressBar->finish();
                $io->newLine(2);
            } else {
                $io->text('Entity scanning completed.');
            }
        }

        $io->text(\sprintf('Found %d referenced file(s) in database.', \count($referencedFiles)));

        // Get a sample mapping to list files
        $sampleClassName = null;
        $sampleField = null;

        foreach ($uploadableClasses as $className) {
            $fields = $this->metadataReader->getUploadableFields($className, $mappingName);

            if (!empty($fields)) {
                $sampleClassName = $className;
                $sampleField = \array_key_first($fields);
                break;
            }
        }

        if (null === $sampleClassName || null === $sampleField) {
            $io->warning('Could not find any uploadable field for this mapping.');

            return 0;
        }

        // Create a fake object to get the mapping
        try {
            $reflectionClass = new \ReflectionClass($sampleClassName);
            $sampleObject = $reflectionClass->newInstanceWithoutConstructor();
            \assert(\is_string($sampleField));
            $mapping = $this->mappingFactory->fromField($sampleObject, $sampleField);
        } catch (\Exception $e) {
            $io->error(\sprintf('Could not create instance of class "%s": %s', $sampleClassName, $e->getMessage()));

            return 0;
        }

        if (null === $mapping) {
            $io->error('Could not create mapping for sample field.');

            return 0;
        }

        // List all files in storage
        $io->text('Scanning storage for files...');

        $storageFiles = [];
        $fileCount = 0;
        $skippedCount = 0;

        foreach ($this->storage->listFiles($mapping) as $storedFile) {
            // Apply min-age filtering if timestamp is available
            // Skip files that are too young (lastModifiedAt > cutoffTimestamp means more recent)
            if (null !== $storedFile->lastModifiedAt && $storedFile->lastModifiedAt > $cutoffTimestamp) {
                // File is too young, skip it
                ++$skippedCount;
                continue;
            }

            $storageFiles[$storedFile->path] = true;
            ++$fileCount;

            // Show progress every 1000 files
            if (0 === $fileCount % 1000) {
                if ($io->isVerbose()) {
                    $io->text(\sprintf('Scanned %d files...', $fileCount));
                }
            }
        }

        if ($skippedCount > 0) {
            $io->text(\sprintf('Skipped %d file(s) younger than cutoff age.', $skippedCount));
        }

        $io->text(\sprintf('Found %d file(s) in storage (matching age criteria).', $fileCount));

        // Find orphaned files
        $orphanedFiles = \array_diff_key($storageFiles, $referencedFiles);

        $io->text(\sprintf('Found %d orphaned file(s).', \count($orphanedFiles)));

        if (empty($orphanedFiles)) {
            return 0;
        }

        // Delete orphaned files
        $deleted = 0;

        if ($io->isVerbose()) {
            $io->text('Orphaned files:');
        }

        foreach (\array_keys($orphanedFiles) as $orphanedFile) {
            if ($io->isVerbose()) {
                $io->text(\sprintf('  - %s', $orphanedFile));
            }

            if (!$dryRun) {
                try {
                    // Parse directory and filename from path
                    $lastSlashPos = \strrpos($orphanedFile, '/');

                    if (false !== $lastSlashPos) {
                        $dir = \substr($orphanedFile, 0, $lastSlashPos);
                        $fileName = \substr($orphanedFile, $lastSlashPos + 1);
                    } else {
                        $dir = '';
                        $fileName = $orphanedFile;
                    }

                    // Create a temporary object to use for deletion
                    // Try to use the constructor if it has no required parameters, otherwise skip it
                    $constructor = $reflectionClass->getConstructor();
                    if (null === $constructor || 0 === $constructor->getNumberOfRequiredParameters()) {
                        $tempObject = $reflectionClass->newInstance();
                    } else {
                        $tempObject = $reflectionClass->newInstanceWithoutConstructor();
                    }
                    $mapping->setFileName($tempObject, $fileName);

                    // Pass directory explicitly to bypass DirectoryNamer
                    // This works even when directory namers are configured, since the directory
                    // is already known from the file path on disk
                    $this->storage->remove($tempObject, $mapping, $dir);
                    ++$deleted;
                } catch (\Exception $e) {
                    $io->error(\sprintf('Failed to delete file "%s": %s', $orphanedFile, $e->getMessage()));
                }
            } else {
                ++$deleted;
            }
        }

        return $deleted;
    }

    private function getManagerForClass(string $className): ?ObjectManager
    {
        foreach ($this->managerRegistries as $managerRegistry) {
            foreach ($managerRegistry->getManagers() as $manager) {
                /** @var class-string $className */
                if (!$manager->getMetadataFactory()->isTransient($className)) {
                    return $manager;
                }
            }
        }

        return null;
    }
}
