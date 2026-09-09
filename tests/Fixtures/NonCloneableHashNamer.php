<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

use Vich\UploaderBundle\Naming\HashNamer;

final class NonCloneableHashNamer extends HashNamer
{
    private function __clone()
    {
    }
}
