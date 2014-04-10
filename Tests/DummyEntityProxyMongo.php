<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ODM\MongoDB\Proxy\Proxy;

/**
 * DummyEntityProxyMongo.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class DummyEntityProxyMongo extends DummyEntity implements Proxy
{
    public function __load() { }

    public function __isInitialized() { }

    public function __setInitialized($initialized) {}

    public function __setInitializer(Closure $initializer = null) {}

    public function __getInitializer() {}

    public function __setCloner(Closure $cloner = null) {}

    public function __getCloner() {}

    public function __getLazyProperties() {}
}
