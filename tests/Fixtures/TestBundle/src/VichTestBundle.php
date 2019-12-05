<?php

namespace Vich\TestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class VichTestBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
