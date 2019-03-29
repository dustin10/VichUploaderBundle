<?php

namespace Vich\UploaderBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
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

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(UploaderHelper::class)->disableOriginalConstructor()->getMock();
        $this->extension = new UploaderExtension($this->helper);
    }

    public function testAssetIsRegistered(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertSame('vich_uploader_asset', $functions[0]->getName());
    }

    public function testAssetForwardsCallsToTheHelper(): void
    {
        $obj = new \stdClass();

        $this->helper
            ->expects($this->once())
            ->method('asset')
            ->with($obj, 'file', 'ClassName');

        $this->extension->asset($obj, 'file', 'ClassName');
    }
}
