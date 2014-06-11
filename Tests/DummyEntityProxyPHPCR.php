<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\Common\Persistence\Proxy;

/**
 * DummyEntityProxyPHPCR.
 *
 * @author Ben Glassman <bglassman@gmail.com>
 */
class DummyEntityProxyPHPCR extends DummyEntity implements Proxy
{
    public function __load() { }

    public function __isInitialized() { }
}

