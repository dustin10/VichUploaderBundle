<?php

namespace Vich\UploaderBundle\Mapping;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Util\PropertyPathUtils;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class PropertyMapping implements PropertyMappingInterface
{
    private const UNKNOWN_PROPERTY_MESSAGE = 'Unknown property %s';
    private const SETTER_PREFIX = 'set';
    private const PROPERTY_WORD_SEPARATORS = ['_', '-'];
    private const PROPERTY_WORD_SEPARATOR = ' ';
    private const EMPTY_STRING = '';

    private ?NamerInterface $namer = null;

    private ?DirectoryNamerInterface $directoryNamer = null;

    private ?array $mapping = null;

    private ?string $mappingName = null;

    /**
     * @var array<string, string|null>
     */
    private array $propertyPaths = [
        'file' => null,
        'name' => null,
        'size' => null,
        'mimeType' => null,
        'originalName' => null,
        'dimensions' => null,
    ];

    private ?PropertyAccessor $accessor = null;

    /**
     * @param string         $filePropertyPath     The path to the "file" property
     * @param string         $fileNamePropertyPath The path to the "filename" property
     * @param array|string[] $propertyPaths        The paths to other properties
     */
    public function __construct(string $filePropertyPath, string $fileNamePropertyPath, array $propertyPaths = [])
    {
        $this->propertyPaths = \array_merge(
            $this->propertyPaths,
            ['file' => $filePropertyPath, 'name' => $fileNamePropertyPath],
            $propertyPaths
        );
    }

    public function getFile(object $obj): ?File
    {
        return $this->readProperty($obj, 'file');
    }

    public function setFile(object $obj, File $file): void
    {
        $this->writeProperty($obj, 'file', $file);
    }

    public function getFileName(object|array $obj): ?string
    {
        return $this->readProperty($obj, 'name');
    }

    public function setFileName(object $obj, string $value): void
    {
        $this->writeProperty($obj, 'name', $value);
    }

    public function erase(object $obj): void
    {
        if (\is_array($this->mapping) && isset($this->mapping['erase_fields']) && false === $this->mapping['erase_fields']) {
            return;
        }

        foreach (['name', 'size', 'mimeType', 'originalName', 'dimensions'] as $property) {
            // Only null out properties that actually accept null. A non-nullable typed
            // property (or setter) would raise a \TypeError here, e.g. when the whole
            // entity is being removed (see #1117).
            if ($this->isNullable($obj, $property)) {
                $this->writeProperty($obj, $property, null);
            }
        }
    }

    /**
     * Tells whether the mapped property can be set to null, i.e. whether erasing it is safe.
     *
     * A property is considered non-nullable when the write path used by the property
     * accessor (a typed setter, or the typed property itself) does not allow null.
     * Unconfigured, dynamic or non-introspectable targets are treated as nullable so the
     * previous behavior is preserved.
     *
     * @throws \InvalidArgumentException
     */
    public function isNullable(object $obj, string $property): bool
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf(self::UNKNOWN_PROPERTY_MESSAGE, $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured: writeProperty() is a no-op
            return true;
        }

        $propertyPath = new PropertyPath(PropertyPathUtils::fixPropertyPath($obj, $this->propertyPaths[$property]));
        $lastIndex = $propertyPath->getLength() - 1;

        if ($propertyPath->isIndex($lastIndex)) {
            // writing into an array offset: no scalar type to violate
            return true;
        }

        $target = $obj;
        if ($lastIndex > 0) {
            $target = $this->getAccessor()->getValue($obj, $propertyPath->getParent());
        }

        if (!\is_object($target)) {
            return true;
        }

        return $this->acceptsNull($target, (string) $propertyPath->getElement($lastIndex));
    }

    private function acceptsNull(object $target, string $name): bool
    {
        // Mirror the property accessor write path: a public camelized setter first,
        // then a public property. Anything else (magic __set, dynamic property) has no
        // static type constraint to violate.
        $setter = self::SETTER_PREFIX.self::camelize($name);
        if (\method_exists($target, $setter) && ($method = new \ReflectionMethod($target, $setter))->isPublic()) {
            $type = ($method->getParameters()[0] ?? null)?->getType();

            return null === $type || $type->allowsNull();
        }

        if (\property_exists($target, $name) && ($property = new \ReflectionProperty($target, $name))->isPublic()) {
            $type = $property->getType();

            return null === $type || $type->allowsNull();
        }

        return true;
    }

    private static function camelize(string $string): string
    {
        return \str_replace(
            self::PROPERTY_WORD_SEPARATOR,
            self::EMPTY_STRING,
            \ucwords(\str_replace(self::PROPERTY_WORD_SEPARATORS, self::PROPERTY_WORD_SEPARATOR, $string))
        );
    }

    public function readProperty(object|array $obj, string $property): mixed
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf(self::UNKNOWN_PROPERTY_MESSAGE, $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured
            return null;
        }

        $propertyPath = PropertyPathUtils::fixPropertyPath($obj, $this->propertyPaths[$property]);

        return $this->getAccessor()->getValue($obj, $propertyPath);
    }

    public function writeProperty(object $obj, string $property, mixed $value): void
    {
        if (!\array_key_exists($property, $this->propertyPaths)) {
            throw new \InvalidArgumentException(\sprintf(self::UNKNOWN_PROPERTY_MESSAGE, $property));
        }

        if (!$this->propertyPaths[$property]) {
            // not configured
            return;
        }

        $propertyPath = PropertyPathUtils::fixPropertyPath($obj, $this->propertyPaths[$property]);
        $this->getAccessor()->setValue($obj, $propertyPath, $value);
    }

    public function getFilePropertyName(): string
    {
        return $this->propertyPaths['file'];
    }

    public function getFileNamePropertyName(): string
    {
        return $this->propertyPaths['name'];
    }

    /**
     * Get the configured namer.
     */
    public function getNamer(): NamerInterface
    {
        return $this->namer ?? throw new \UnexpectedValueException('No namer has been configured.');
    }

    /**
     * Set the namer.
     */
    public function setNamer(NamerInterface $namer): void
    {
        $this->namer = $namer;
    }

    /**
     * Determine if the mapping has a custom namer configured.
     */
    public function hasNamer(): bool
    {
        return null !== $this->namer;
    }

    /**
     * Get the configured directory namer.
     */
    public function getDirectoryNamer(): ?DirectoryNamerInterface
    {
        return $this->directoryNamer;
    }

    /**
     * Set the directory namer.
     */
    public function setDirectoryNamer(DirectoryNamerInterface $directoryNamer): void
    {
        $this->directoryNamer = $directoryNamer;
    }

    /**
     * Determine if the mapping has a custom directory namer configured.
     */
    public function hasDirectoryNamer(): bool
    {
        return null !== $this->directoryNamer;
    }

    /**
     * Set the configured configuration mapping.
     *
     * @param array $mapping The mapping;
     */
    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }

    public function getMappingName(): string
    {
        return $this->mappingName;
    }

    /**
     * Set the configured configuration mapping name.
     */
    public function setMappingName(string $mappingName): void
    {
        $this->mappingName = $mappingName;
    }

    public function getUploadName(object $obj): string
    {
        return $this->getNamer()->name($obj, $this);
    }

    public function getUploadDir(object|array $obj): ?string
    {
        if (!$this->hasDirectoryNamer()) {
            return '';
        }

        $dir = $this->getDirectoryNamer()?->directoryName($obj, $this);

        // strip the trailing directory separator if needed
        return $dir ? \rtrim($dir, '/\\') : $dir;
    }

    public function getUploadDestination(): string
    {
        return $this->mapping['upload_destination'];
    }

    public function getUriPrefix(): string
    {
        return $this->mapping['uri_prefix'];
    }

    private function getAccessor(): PropertyAccessor
    {
        // TODO: reuse original property accessor from forms
        if (null !== $this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
