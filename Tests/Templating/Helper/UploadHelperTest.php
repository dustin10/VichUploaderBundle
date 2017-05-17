<?php

namespace Vich\UploaderBundle\Tests\Templating\Helper;

use PHPUnit\Framework\TestCase;
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

    protected function setUp()
    {
        $this->storage = $this->createMock('Vich\UploaderBundle\Storage\StorageInterface');
        $this->helper = new UploaderHelper($this->storage);
    }

    public function testGetName()
    {
        $this->assertSame('vich_uploader', $this->helper->getName());
    }

    public function testAssetForwardsCallsToTheStorage()
    {
        $obj = new \stdClass();

        $this->storage
            ->expects($this->once())
            ->method('resolveUri')
            ->with($obj, 'file', 'ClassName');

        $this->helper->asset($obj, 'file', 'ClassName');
    }
}
