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
final class UploaderExtensionTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UploaderHelper
     */
    protected $helper;

    protected \Vich\UploaderBundle\Twig\Extension\UploaderExtension $extension;

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(UploaderHelper::class)->disableOriginalConstructor()->getMock();
        $this->extension = new UploaderExtension($this->helper);
    }

    public function testAssetIsRegistered(): void
    {
        $functions = $this->extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertSame('vich_uploader_asset', $functions[0]->getName());
    }

    public function testAssetForwardsCallsToTheHelper(): void
    {
        $obj = new \stdClass();

        $this->helper
            ->expects(self::once())
            ->method('asset')
            ->with($obj, 'file');

        $this->extension->asset($obj, 'file');
    }
}
