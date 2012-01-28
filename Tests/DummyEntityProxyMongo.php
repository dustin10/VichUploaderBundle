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
}
