<?php

namespace Vich\UploaderBundle\Tests\Adapter\Propel;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Adapter\Propel\PropelORMAdapter;

/**
 * PropelORMAdapterTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class PropelORMAdapterTest extends TestCase
{
    protected $adapter;

    public static function setUpBeforeClass()
    {
        if (!class_exists('Symfony\Component\EventDispatcher\GenericEvent')) {
            self::markTestSkipped('Symfony\Component\EventDispatcher\GenericEvent does not exist.');
        }
    }

    protected function setUp()
    {
        $this->adapter = new PropelORMAdapter();
    }

    public function testGetObjectFromArgs()
    {
        $event = $this->createMock('\Symfony\Component\EventDispatcher\GenericEvent');
        $event
            ->expects($this->once())
            ->method('getSubject')
            ->will($this->returnValue(42));

        $this->assertSame(42, $this->adapter->getObjectFromArgs($event));
    }

    public function testRecomputeChangeset()
    {
        $event = $this->createMock('\Symfony\Component\EventDispatcher\GenericEvent');

        // does nothing but should be callable
        $this->adapter->recomputeChangeSet($event);
        $this->assertTrue(true);    // this workaround is needed to avoid Risky test
    }
}
