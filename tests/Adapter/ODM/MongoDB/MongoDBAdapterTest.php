<?php

namespace Vich\UploaderBundle\Tests\Adapter\ODM\MongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
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
    public function testRecomputeChangeSet(): void
    {
        $entity = new DummyEntity();
        $changeSet = [
            'fileName' => [
                'test.csv',
                'test2.csv',
            ],
        ];
        $dm = $this->createStub(DocumentManager::class);
        $metadata = new ClassMetadata(DummyEntity::class);
        $dm->method('getClassMetadata')->willReturn($metadata);
        $event = new PreUpdateEventArgs($entity, $dm, $changeSet);

        $adapter = new MongoDBAdapter();
        $uow = $this->createMock(UnitOfWork::class);
        $dm->method('getUnitOfWork')->willReturn($uow);
        $uow->persist($entity);
        $uow->expects(self::once())->method('recomputeSingleDocumentChangeSet')->with($metadata, $entity);
        $adapter->recomputeChangeSet($event);
    }
}
