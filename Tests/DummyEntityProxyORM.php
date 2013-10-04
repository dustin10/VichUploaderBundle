<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\Common\Persistence\Proxy;

/**
 * DummyEntityProxyORM.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DummyEntityProxyORM extends DummyEntity implements Proxy
{
    public function __load() { }

    public function __isInitialized() { }
}
