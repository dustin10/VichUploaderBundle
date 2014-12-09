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
     * @var UploaderHelper $helper
     */
    private $helper;

    /**
     * Constructs a new instance of UploaderExtension.
     *
     * @param UploaderHelper $helper
     */
    public function __construct(UploaderHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'vich_uploader';
    }

    /**
     * Returns a list of twig functions.
     *
     * @return array An array
     */
    public function getFunctions()
    {
        $names = array(
            'vich_uploader_asset' => 'asset'
        );

        $funcs = array();
        foreach ($names as $twig => $local) {
            $funcs[$twig] = new \Twig_Function_Method($this, $local);
        }

        return $funcs;
    }

    /**
     * Gets the public path for the file associated with the uploadable
     * object.
     *
     * @param object $obj       The object.
     * @param string $fieldName The field name.
     * @param string $className The object's class. Mandatory if $obj can't be used to determine it.
     *
     * @return string The public path.
     */
    public function asset($obj, $fieldName, $className = null)
    {
        return $this->helper->asset($obj, $fieldName, $className);
    }
}
