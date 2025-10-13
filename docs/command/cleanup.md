# Cleanup Command

Removes orphaned files from storage that are no longer referenced in the database.

This command is useful when files have been deleted from the database (e.g., through
foreign key cascades or direct database operations) but the physical files remain in
storage.

## ⚠️ Safety & Limitations

**BEFORE RUNNING THIS COMMAND:**

- **ALWAYS BACKUP YOUR STORAGE—**file deletions are permanent and cannot be rolled
  back. There is no undo.
- **TEST WITH `--dry-run` FIRST** - always preview what will be deleted before running
  the actual clean-up.

**Built-in safety:**

- Files younger than `--min-age` (default: 60 minutes) are never deleted to prevent race
  conditions with concurrent uploads
- Interactive mode requires explicit "yes" confirmation before deleting

**Limitations:**

- Requires repositories that support `createQueryBuilder()`. Custom repositories without
  this method will be skipped with a warning
- For remote storage backends (S3, Azure, etc.), file timestamps may not be available
  and `--min-age` protection may not work correctly
- Scripts and CI/CD must explicitly use `--force` or `--dry-run` (interactive mode is
  not available in non-interactive environments)

## Basic usage

```bash
# Preview what would be deleted
php bin/console vich:cleanup --dry-run

# Interactive mode (asks for confirmation)
php bin/console vich:cleanup

# Non-interactive mode for scripts/CI-CD
php bin/console vich:cleanup --force
```

## Options

- `--dry-run`: Preview which files would be deleted without actually deleting them
- `--force`: Skip confirmation prompt (required for non-interactive execution)
- `--mapping=MAPPING` (`-m`): Process only a specific mapping instead of all mappings
- `--batch-size=SIZE` (`-b`): Number of entities to process per batch (default: 1000,
  max: 10000)
- `--min-age=MINUTES`: Minimum age in minutes for files to be considered orphaned
  (default: 60 minutes)
- `--verbose` (`-v`): Show detailed progress information including list of all orphaned
  files

## Common examples

```bash
# Preview cleanup for a specific mapping
php bin/console vich:cleanup --mapping=product_image --dry-run

# Run cleanup for a specific mapping
php bin/console vich:cleanup --mapping=product_image --force

# Extra safety: only delete files older than 2 hours
php bin/console vich:cleanup --min-age=120 --force

# Verbose output with file details
php bin/console vich:cleanup --dry-run -v

# Memory-constrained environments
php bin/console vich:cleanup --batch-size=500 --force

# Immediate cleanup (USE WITH CAUTION - may delete files being uploaded)
php bin/console vich:cleanup --min-age=0 --force
```

[Return to commands overview](../commands.md)
