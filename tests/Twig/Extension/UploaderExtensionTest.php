<?php

namespace Vich\UploaderBundle\Tests\Twig\Extension;

use PHPUnit\Framework\Attributes\Test;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;
use Vich\UploaderBundle\Tests\TestCase;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;
use Vich\UploaderBundle\Twig\Extension\UploaderExtensionRuntime;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
final class UploaderExtensionTest extends TestCase
{
    #[Test]
    public function assetIsRegistered(): void
    {
        $extension = new UploaderExtension();
        $functions = $extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertSame('vich_uploader_asset', $functions[0]->getName());
    }

    #[Test]
    public function assetForwardsCallsToTheHelper(): void
    {
        $helper = $this->createMock(UploaderHelperInterface::class);
        $extension = new UploaderExtensionRuntime($helper);
        $object = new \stdClass();

        $helper
            ->expects($this->once())
            ->method('asset')
            ->with($object, 'file');
        $extension->asset($object, 'file');
    }
}
