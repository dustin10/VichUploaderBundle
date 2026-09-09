<?php

namespace Vich\UploaderBundle\Mapping;

use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Naming\ChainDirectoryNamer;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\ImmutableConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Util\ClassUtils;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 *
 * @internal
 */
final readonly class PropertyMappingResolver implements PropertyMappingResolverInterface
{
    /**
     * @param iterable<string, NamerInterface>          $namers
     * @param iterable<string, DirectoryNamerInterface> $dirNamers
     */
    public function __construct(
        private iterable $namers,
        private iterable $dirNamers,
        private array $mappings,
        private ?string $defaultFilenameAttributeSuffix = '_name'
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
            $namer = $this->getNamer($mappingData['mapping'], $namerConfig['service']);

            $options = $namerConfig['options'] ?? [];

            // Handle namer_keep_extension option
            if (isset($config['namer_keep_extension']) && $config['namer_keep_extension']) {
                if (!$namer instanceof ImmutableConfigurableInterface && !$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s does not implement ImmutableConfigurableInterface but namer_keep_extension option is set to true in mapping "%s". Either make the namer implement ImmutableConfigurableInterface or remove the namer_keep_extension option.', $namerConfig['service'], $mappingData['mapping']));
                }
                $options['keep_extension'] = $config['namer_keep_extension'];
            }

            $namer = $this->configureNamer($namer, $options, $namerConfig['service']);

            $mapping->setNamer($namer);
        }

        if (!empty($config['directory_namer']) && null !== $config['directory_namer']['service']) {
            $mapping->setDirectoryNamer($this->resolveDirectoryNamer($mappingData['mapping'], $config['directory_namer']));
        }

        return $mapping;
    }

    /**
     * @param array{service: string, options?: array<string, mixed>|null} $config
     */
    private function resolveDirectoryNamer(string $mappingName, array $config): DirectoryNamerInterface
    {
        $namer = $this->getDirectoryNamer($mappingName, $config['service']);
        $options = $config['options'] ?? [];

        if ($namer instanceof ChainDirectoryNamer && \array_key_exists('namers', $options)) {
            if (!\is_array($options['namers'])) {
                throw new \InvalidArgumentException('The "namers" option of ChainDirectoryNamer must be an array.');
            }
            $children = [];
            foreach ($options['namers'] as $nestedConfig) {
                if (!\is_array($nestedConfig) || !isset($nestedConfig['service']) || !\is_string($nestedConfig['service'])) {
                    throw new \InvalidArgumentException('Each chained directory namer must specify a service.');
                }
                $children[] = $this->resolveDirectoryNamer($mappingName, $nestedConfig);
            }
            $options['namers'] = $children;
        }

        return $this->configureNamer($namer, $options, $config['service']);
    }

    /**
     * @template T of NamerInterface|DirectoryNamerInterface
     *
     * @param T                    $namer
     * @param array<string, mixed> $options
     *
     * @return T
     */
    private function configureNamer(NamerInterface|DirectoryNamerInterface $namer, array $options, string $service): NamerInterface|DirectoryNamerInterface
    {
        if ($namer instanceof ImmutableConfigurableInterface) {
            $configured = $namer->withOptions($options);
            if ($configured === $namer) {
                throw new \LogicException(\sprintf('Namer "%s" must return a new instance from withOptions().', $service));
            }

            return $configured;
        }

        // Legacy path: the shared service is configured in place, so every mapping using it ends
        // up with the options of the last one resolved.
        if ($namer instanceof ConfigurableInterface) {
            if ([] !== $options) {
                trigger_deprecation('vich/uploader-bundle', '3.1', 'Configuring namer "%s" through "%s" is deprecated, implement "%s" instead.', $service, ConfigurableInterface::class, ImmutableConfigurableInterface::class);

                $namer->configure($options);
            }

            return $namer;
        }

        if ([] !== $options) {
            throw new \LogicException(\sprintf('Namer %s can not receive options as it does not implement ImmutableConfigurableInterface.', $service));
        }

        return $namer;
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
