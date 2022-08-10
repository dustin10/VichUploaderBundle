<?php

namespace Vich\UploaderBundle\Tests\Adapter\ODM\MongoDB;

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Hydrator\HydratorFactory;
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
        $dm->method('getClassMetadata')->willReturn(new ClassMetadata(DummyEntity::class));
        $event = new PreUpdateEventArgs($entity, $dm, $changeSet);

        $adapter = new MongoDBAdapter();
        $uow = $this->getUnitOfWork($dm);
        $dm->method('getUnitOfWork')->willReturn($uow);
        $uow->persist($entity);

        $uow->setDocumentChangeSet($entity, $changeSet);

        self::assertSame($changeSet, $uow->getDocumentChangeSet($entity));
        $adapter->recomputeChangeSet($event);
        self::assertSame([], $uow->getDocumentChangeSet($entity));
    }

    protected function getUnitOfWork(DocumentManager $documentManager): UnitOfWork
    {
        $eventManager = $this->createStub(EventManager::class);
        $hydratorFactory = new HydratorFactory(
            $documentManager,
            $eventManager,
            '/tmp',
            'Hydrator',
            Configuration::AUTOGENERATE_NEVER
        );

        return new UnitOfWork($documentManager, $eventManager, $hydratorFactory);
    }
}
