<?php

namespace Vich\UploaderBundle\Tests\Twig\Extension;

use Vich\UploaderBundle\Twig\Extension\UploaderExtension;

/**
 * UploaderExtensionTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploaderExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $extension;

    public function setUp()
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
        $expectedFunction = new \Twig_SimpleFunction('vich_uploader_asset', array($this->extension, 'asset'));

        $this->assertEquals(array($expectedFunction), $functions);
    }

    public function testAssetForwardsCallsToTheHelper()
    {
        $obj = new \stdClass;

        $this->helper
            ->expects($this->once())
            ->method('asset')
            ->with($obj, 'file', 'ClassName');

        $this->extension->asset($obj, 'file', 'ClassName');
    }
}
