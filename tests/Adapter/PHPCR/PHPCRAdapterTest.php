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
    public function testGetChangeSet(): void
    {
        $entity = new DummyEntity();
        $changeSet = [
            'fileName' => [
                'test.csv',
                'test2.csv',
            ],
        ];
        $event = new PreUpdateEventArgs($entity, $this->createStub(ObjectManager::class), $changeSet);

        $adapter = new PHPCRAdapter();

        self::assertSame($changeSet, $adapter->getChangeSet($event));
    }
}
