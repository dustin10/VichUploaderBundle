<?php

namespace Vich\UploaderBundle\Tests\Adapter\Propel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Vich\UploaderBundle\Adapter\Propel\PropelORMAdapter;

/**
 * PropelORMAdapterTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelORMAdapterTest extends TestCase
{
    protected $adapter;

    public static function setUpBeforeClass(): void
    {
        if (!\class_exists(GenericEvent::class)) {
            self::markTestSkipped('Symfony\Component\EventDispatcher\GenericEvent does not exist.');
        }
    }

    protected function setUp(): void
    {
        $this->adapter = new PropelORMAdapter();
    }

    public function testGetObjectFromArgs(): void
    {
        $event = $this->createMock(GenericEvent::class);
        $event
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn(42);

        $this->assertSame(42, $this->adapter->getObjectFromArgs($event));
    }

    public function testRecomputeChangeset(): void
    {
        $event = $this->createMock(GenericEvent::class);

        // does nothing but should be callable
        $this->adapter->recomputeChangeSet($event);
        $this->assertTrue(true);    // this workaround is needed to avoid Risky test
    }
}
