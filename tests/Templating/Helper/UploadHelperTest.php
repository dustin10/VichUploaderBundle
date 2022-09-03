<?php

namespace Vich\UploaderBundle\Tests\Templating\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * FileInjectorTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class UploadHelperTest extends TestCase
{
    protected StorageInterface|MockObject $storage;

    protected UploaderHelper $helper;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->helper = new UploaderHelper($this->storage);
    }

    public function testGetName(): void
    {
        self::assertSame('vich_uploader', $this->helper->getName());
    }

    public function testAssetForwardsCallsToTheStorage(): void
    {
        $obj = new \stdClass();

        $this->storage
            ->expects(self::once())
            ->method('resolveUri')
            ->with($obj, 'file');

        $this->helper->asset($obj, 'file');
    }
}
