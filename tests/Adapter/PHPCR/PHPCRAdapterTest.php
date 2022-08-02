<?php

namespace Vich\UploaderBundle\Tests\Adapter\PHPCR;

use Doctrine\Persistence\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\PHPCR\PHPCRAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 */
final class PHPCRAdapterTest extends TestCase
{
    public function testRecomputeChangeSet(): void
    {
        $entity = new DummyEntity();
        $changeSet = [];

        $uow = $this->createMock(\stdClass::class);
        $om = $this->createMock(ObjectManager::class);

        $this->markTestIncomplete();
        $om->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($entity);

        $event = new PreUpdateEventArgs($entity, $om, $changeSet);

        $adapter = new PHPCRAdapter();
        $adapter->recomputeChangeSet($event);
    }
}
