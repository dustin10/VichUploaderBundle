<?php

namespace Vich\UploaderBundle\Tests\Templating\Helper;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * FileInjectorTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadHelperTest extends TestCase
{
    protected $storage;

    protected $helper;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->helper = new UploaderHelper($this->storage);
    }

    public function testGetName(): void
    {
        $this->assertSame('vich_uploader', $this->helper->getName());
    }

    public function testAssetForwardsCallsToTheStorage(): void
    {
        $obj = new \stdClass();

        $this->storage
            ->expects($this->once())
            ->method('resolveUri')
            ->with($obj, 'file', 'ClassName');

        $this->helper->asset($obj, 'file', 'ClassName');
    }
}
