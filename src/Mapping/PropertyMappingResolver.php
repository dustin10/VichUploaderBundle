<?php

namespace Vich\UploaderBundle\Mapping;

use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Naming\Base64Namer;
use Vich\UploaderBundle\Naming\ConfigurableDirectoryNamer;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\HashNamer;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\OrignameNamer;
use Vich\UploaderBundle\Naming\PropertyDirectoryNamer;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Naming\SlugNamer;
use Vich\UploaderBundle\Naming\SmartUniqueNamer;
use Vich\UploaderBundle\Naming\SubdirDirectoryNamer;
use Vich\UploaderBundle\Naming\UniqidNamer;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final class PropertyMappingResolver implements PropertyMappingResolverInterface
{
    /**
     * @param iterable<string, NamerInterface>          $namers
     * @param iterable<string, DirectoryNamerInterface> $dirNamers
     */
    public function __construct(
        private readonly iterable $namers,
        private readonly iterable $dirNamers,
        private readonly array $mappings,
        private readonly ?string $defaultFilenameAttributeSuffix = '_name'
    ) {
    }

    public function resolve(object|array $obj, string $fieldName, array $mappingData): PropertyMapping
    {
        if (!\array_key_exists($mappingData['mapping'], $this->mappings)) {
            $className = \is_object($obj) ? ClassUtils::getClass($obj) : '[array]';
            throw MappingNotFoundException::createNotFoundForClassAndField($mappingData['mapping'], $className, $fieldName);
        }

        $config = $this->mappings[$mappingData['mapping']];
        $fileProperty = $mappingData['propertyName'] ?? $fieldName;
        $fileNameProperty = empty($mappingData['fileNameProperty']) ? $fileProperty.$this->defaultFilenameAttributeSuffix : $mappingData['fileNameProperty'];

        $mapping = new PropertyMapping($fileProperty, $fileNameProperty, $mappingData);
        $mapping->setMappingName($mappingData['mapping']);
        $mapping->setMapping($config);

        if (!empty($config['namer']) && null !== $config['namer']['service']) {
            $namerConfig = $config['namer'];
            $namer = $this->copyBuiltInNamer($this->getNamer($mappingData['mapping'], $namerConfig['service']));

            $options = $namerConfig['options'] ?? [];

            // Handle namer_keep_extension option
            if (isset($config['namer_keep_extension']) && $config['namer_keep_extension']) {
                if (!$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s does not implement ConfigurableInterface but namer_keep_extension option is set to true in mapping "%s". Either make the namer implement ConfigurableInterface or remove the namer_keep_extension option.', $namerConfig['service'], $mappingData['mapping']));
                }
                $options['keep_extension'] = $config['namer_keep_extension'];
            }

            if (!empty($options)) {
                if (!$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s can not receive options as it does not implement ConfigurableInterface.', $namerConfig['service']));
                }
                $namer->configure($options);
            }

            $mapping->setNamer($namer);
        }

        if (!empty($config['directory_namer']) && null !== $config['directory_namer']['service']) {
            $namerConfig = $config['directory_namer'];
            $namer = $this->copyBuiltInNamer($this->getDirectoryNamer($mappingData['mapping'], $namerConfig['service']));

            if (!empty($namerConfig['options'])) {
                if (!$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s can not receive options as it does not implement ConfigurableInterface.', $namerConfig['service']));
                }
                $namer->configure($namerConfig['options']);
            }

            $mapping->setDirectoryNamer($namer);
        }

        return $mapping;
    }

    /**
     * Built-in namers keep their configuration in scalar properties, so a shallow copy preserves
     * service defaults without sharing mapping options. Copy even when no options are supplied:
     * a retained mapping must not observe later configuration changes to the service.
     *
     * Match concrete classes only. Custom namers, subclasses and decorators have no cloning
     * contract in 2.x and must retain their existing configuration and service lifecycle.
     *
     * @template T of NamerInterface|DirectoryNamerInterface
     *
     * @param T $namer
     *
     * @return T
     */
    private function copyBuiltInNamer(NamerInterface|DirectoryNamerInterface $namer): NamerInterface|DirectoryNamerInterface
    {
        return \in_array($namer::class, [
            Base64Namer::class,
            ConfigurableDirectoryNamer::class,
            CurrentDateTimeDirectoryNamer::class,
            HashNamer::class,
            OrignameNamer::class,
            PropertyDirectoryNamer::class,
            PropertyNamer::class,
            SlugNamer::class,
            SmartUniqueNamer::class,
            SubdirDirectoryNamer::class,
            UniqidNamer::class,
        ], true) ? clone $namer : $namer;
    }

    private function getNamer(string $name, string $service): NamerInterface
    {
        $altService = \substr($service, 0, -\strlen($name) - 1);
        foreach ($this->namers as $id => $namer) {
            if ($id === $service || $namer::class === $service || $altService === $service || $altService === $id) {
                return $namer;
            }
        }

        throw new \UnexpectedValueException(\sprintf('Namer service "%s" not found.', $service));
    }

    private function getDirectoryNamer(string $name, string $service): DirectoryNamerInterface
    {
        $altService = \substr($service, 0, -\strlen($name) - 1);
        foreach ($this->dirNamers as $id => $namer) {
            if ($id === $service || $namer::class === $service || $altService === $service || $altService === $id) {
                return $namer;
            }
        }

        throw new \UnexpectedValueException(\sprintf('Directory namer service "%s" not found.', $service));
    }
}
