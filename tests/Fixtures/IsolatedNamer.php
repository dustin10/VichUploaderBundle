<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\ImmutableConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

#[AsTaggedItem(index: 'custom')]
final class IsolatedNamer implements NamerInterface, DirectoryNamerInterface, ImmutableConfigurableInterface
{
    public function __construct(private \stdClass $configuration = new \stdClass())
    {
    }

    private function __clone()
    {
    }

    public function withOptions(array $options): static
    {
        $namer = new self(clone $this->configuration);
        $namer->configure($options);

        return $namer;
    }

    public function configure(array $options): void
    {
        if (isset($options['prefix'])) {
            $this->configuration->prefix = $options['prefix'];
        }
    }

    public function name(object|array $object, PropertyMappingInterface $mapping): string
    {
        return $this->configuration->prefix ?? 'default';
    }

    public function directoryName(object|array $object, PropertyMappingInterface $mapping): string
    {
        return $this->name($object, $mapping);
    }
}
