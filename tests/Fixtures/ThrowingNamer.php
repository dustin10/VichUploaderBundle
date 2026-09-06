<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

final class ThrowingNamer implements NamerInterface
{
    public function __construct()
    {
        throw new \RuntimeException('An unrelated namer must not be instantiated.');
    }

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        return 'unused';
    }
}
