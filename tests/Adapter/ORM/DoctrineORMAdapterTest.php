<?php

namespace Vich\UploaderBundle\Tests\Adapter\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class DoctrineORMAdapterTest extends TestCase
{
    public function testRecomputeChangeSet(): void
    {
        $entity = new DummyEntity();
        $changeSet = [];

        // cannot be mocked due to final class
        // $uow = $this->createMock(\Doctrine\ORM\UnitOfWork::class);
        $em = $this->createMock(EntityManager::class);
        $metadata = $this->createMock(ClassMetadata::class);

        /*
        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($uow);
        */

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with(DummyEntity::class)
            ->willReturn($metadata);

        /*
        $uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $entity);
        */

        $event = new PreUpdateEventArgs($entity, $em, $changeSet);

        $adapter = new DoctrineORMAdapter();
        $adapter->recomputeChangeSet($event);
    }
}
