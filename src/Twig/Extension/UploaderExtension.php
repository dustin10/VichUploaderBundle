<?php

namespace Vich\UploaderBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class UploaderExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('vich_uploader_asset', [UploaderExtensionRuntime::class, 'asset']),
        ];
    }
}
