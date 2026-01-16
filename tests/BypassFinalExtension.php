<?php

namespace Vich\UploaderBundle\Tests;

use DG\BypassFinals;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class BypassFinalExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        BypassFinals::denyPaths(['*/vendor/phpunit/*']);
        BypassFinals::enable();
    }
}
