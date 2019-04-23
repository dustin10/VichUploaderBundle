<?php

namespace Vich\UploaderBundle\Twig\Extension;

use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * UploaderExtension.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class UploaderExtension extends \Twig_Extension
{
    /**
     * @var UploaderHelper
     */
    private $helper;

    public function __construct(UploaderHelper $helper)
    {
        $this->helper = $helper;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('vich_uploader_asset', [$this, 'asset']),
        ];
    }

    /**
     * Gets the public path for the file associated with the uploadable object.
     *
     * @param object $obj       The object
     * @param string $fieldName The field name
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return string|null The public path or null if file not stored
     */
    public function asset($obj, string $fieldName, string $className = null): ?string
    {
        return $this->helper->asset($obj, $fieldName, $className);
    }
}
