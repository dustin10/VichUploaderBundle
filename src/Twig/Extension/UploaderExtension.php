<?php

namespace Vich\UploaderBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

/**
 * UploaderExtension.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class UploaderExtension extends AbstractExtension
{
    public function __construct(private readonly UploaderHelperInterface $helper)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vich_uploader_asset', $this->asset(...)),
        ];
    }

    /**
     * Gets the public path for the file associated with the uploadable object.
     *
     * @param object      $object    The object
     * @param string|null $fieldName The field name
     *
     * @return string|null The public path or null if file not stored
     */
    public function asset(object $object, ?string $fieldName = null): ?string
    {
        return $this->helper->asset($object, $fieldName);
    }
}
