<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;

final class NonCloneableNamer implements NamerInterface, DirectoryNamerInterface, ConfigurableInterface
{
    private \stdClass $configuration;

    public int $configurationCalls = 0;

    public function __construct()
    {
        $this->configuration = (object) ['prefix' => 'default'];
    }

    public static function getId(): string
    {
        return 'custom';
    }

    private function __clone()
    {
    }

    public function configure(array $options): void
    {
        ++$this->configurationCalls;
        $this->configuration->prefix = $options['prefix'];
    }

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        return $this->configuration->prefix;
    }

    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        return $this->configuration->prefix;
    }
}
