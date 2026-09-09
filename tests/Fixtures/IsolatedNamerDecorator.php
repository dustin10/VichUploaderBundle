<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Naming\ImmutableConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

#[AsTaggedItem(index: 'custom')]
final class IsolatedNamerDecorator implements NamerInterface, ImmutableConfigurableInterface
{
    public function __construct(private readonly NamerInterface&ImmutableConfigurableInterface $inner)
    {
    }

    public function withOptions(array $options): static
    {
        return new self($this->inner->withOptions($options));
    }

    public function name(object|array $object, PropertyMappingInterface $mapping): string
    {
        return 'decorated-'.$this->inner->name($object, $mapping);
    }
}
