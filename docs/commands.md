# Commands

## Debug class

Show entity file mapping metadata for a class.

```bash
php bin/console vich:mapping:debug-class App\\Entity\\Foo
```

## Debug mapping

Show mapping details.

```bash
php bin/console vich:mapping:debug foo_mapping
```

## Uploadable classes

Searches for uploadable classes.

```bash
php bin/console vich:mapping:list-classes
```

## Cleanup orphaned files

Removes orphaned files from storage that are no longer referenced in the database.

```bash
php bin/console vich:cleanup --dry-run
```

For detailed documentation, options, examples, and safety considerations, see the
[cleanup command documentation](command/cleanup.md).

[Return to the index](index.md)
