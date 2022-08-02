<?php

namespace Vich\UploaderBundle\Tests\Adapter\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * DoctrineORMAdapterTest.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class DoctrineORMAdapterTest extends TestCase
{
    public function testRecomputeChangeSet(): void
    {
        $entity = new DummyEntity();
        $changeSet = [];

        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManager::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $em->expects(self::once())
            ->method('getClassMetadata')
            ->with(DummyEntity::class)
            ->willReturn($metadata);

        $uow->expects(self::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $entity);

        $event = new PreUpdateEventArgs($entity, $em, $changeSet);

        $adapter = new DoctrineORMAdapter();
        $adapter->recomputeChangeSet($event);
    }
}
