<?php

namespace Vich\UploaderBundle\Tests\Templating\Helper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class UploadHelperTest extends TestCase
{
    protected StorageInterface&Stub $storage;

    protected UploaderHelper $helper;

    protected function setUp(): void
    {
        $this->storage = $this->createStub(StorageInterface::class);
        $this->helper = new UploaderHelper($this->storage);
    }

    #[Test]
    public function getName(): void
    {
        self::assertSame('vich_uploader', $this->helper->getName());
    }

    #[Test]
    public function assetForwardsCallsToTheStorage(): void
    {
        $obj = new \stdClass();

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('resolveUri')
            ->with($obj, 'file');

        (new UploaderHelper($storage))->asset($obj, 'file');
    }
}
