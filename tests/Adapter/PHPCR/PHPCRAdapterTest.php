<?php

namespace Vich\UploaderBundle\Tests\Adapter\PHPCR;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\PHPCR\PHPCRAdapter;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Ben Glassman <bglassman@gmail.com>
 */
final class PHPCRAdapterTest extends TestCase
{
    /**
     * @requires function LifecycleEventArgs::getObject
     */
    public function testGetObjectFromArgs(): void
    {
        $entity = new DummyEntity();

        $args = $this->createMock(LifecycleEventArgs::class);
        $args
            ->expects(self::once())
            ->method('getObject')
            ->willReturn($entity);

        $adapter = new PHPCRAdapter();

        self::assertEquals($entity, $adapter->getObjectFromArgs($args));
    }
}
