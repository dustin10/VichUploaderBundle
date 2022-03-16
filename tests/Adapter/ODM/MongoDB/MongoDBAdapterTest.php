<?php

namespace Vich\UploaderBundle\Tests\Adapter\ODM\MongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\ODM\MongoDB\MongoDBAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * MongoDBAdapterTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class MongoDBAdapterTest extends TestCase
{
    public function testGetChangeSet(): void
    {
        $entity = new DummyEntity();
        $changeSet = [
            'fileName' => [
                'test.csv',
                'test2.csv',
            ],
        ];
        $event = new PreUpdateEventArgs($entity, $this->createStub(DocumentManager::class), $changeSet);

        $adapter = new MongoDBAdapter();

        self::assertSame($changeSet, $adapter->getChangeSet($event));
    }
}
