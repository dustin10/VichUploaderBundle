<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Mapping;

use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
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
            $namer = $this->getNamer($mappingData['mapping'], $namerConfig['service']);

            if (!empty($namerConfig['options'])) {
                if (!$namer instanceof ConfigurableInterface) {
                    throw new \LogicException(\sprintf('Namer %s can not receive options as it does not implement ConfigurableInterface.', $namerConfig['service']));
                }
                $namer->configure($namerConfig['options']);
            }

            $mapping->setNamer($namer);
        }

        if (!empty($config['directory_namer']) && null !== $config['directory_namer']['service']) {
            $namerConfig = $config['directory_namer'];
            $namer = $this->getDirectoryNamer($mappingData['mapping'], $namerConfig['service']);

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
