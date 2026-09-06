<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

final class NamerDecorator implements NamerInterface, ConfigurableInterface
{
    public function __construct(private readonly NamerInterface&ConfigurableInterface $inner)
    {
    }

    public static function getId(): string
    {
        return 'custom';
    }

    public function configure(array $options): void
    {
        $this->inner->configure($options);
    }

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        return 'decorated-'.$this->inner->name($object, $mapping);
    }
}
