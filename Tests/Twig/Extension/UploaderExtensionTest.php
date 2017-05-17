<?php

namespace Vich\UploaderBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;

/**
 * UploaderExtensionTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploaderExtensionTest extends TestCase
{
    protected $helper;
    protected $extension;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder('Vich\UploaderBundle\Templating\Helper\UploaderHelper')->disableOriginalConstructor()->getMock();
        $this->extension = new UploaderExtension($this->helper);
    }

    public function testGetName()
    {
        $this->assertSame('vich_uploader', $this->extension->getName());
    }

    public function testAssetIsRegistered()
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertSame('vich_uploader_asset', $functions[0]->getName());
    }

    public function testAssetForwardsCallsToTheHelper()
    {
        $obj = new \stdClass();

        $this->helper
            ->expects($this->once())
            ->method('asset')
            ->with($obj, 'file', 'ClassName');

        $this->extension->asset($obj, 'file', 'ClassName');
    }
}
