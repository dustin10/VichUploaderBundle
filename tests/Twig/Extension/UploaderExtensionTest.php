<?php

namespace Vich\UploaderBundle\Tests\Twig\Extension;

use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;
use Vich\UploaderBundle\Tests\TestCase;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;
use Vich\UploaderBundle\Twig\Extension\UploaderExtensionRuntime;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class UploaderExtensionTest extends TestCase
{
    public function testAssetIsRegistered(): void
    {
        $extension = new UploaderExtension();
        $functions = $extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertSame('vich_uploader_asset', $functions[0]->getName());
    }

    public function testAssetForwardsCallsToTheHelper(): void
    {
        $helper = $this->createMock(UploaderHelperInterface::class);
        $extension = new UploaderExtensionRuntime($helper);
        $object = new \stdClass();

        $helper
            ->expects(self::once())
            ->method('asset')
            ->with($object, 'file');
        $extension->asset($object, 'file');
    }
}
