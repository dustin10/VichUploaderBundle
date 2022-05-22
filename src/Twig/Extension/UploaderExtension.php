<?php

namespace Vich\UploaderBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

/**
 * UploaderExtension.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
final class UploaderExtension extends AbstractExtension
{
    /**
     * @var UploaderHelperInterface
     */
    private $helper;

    public function __construct(UploaderHelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vich_uploader_asset', [$this, 'asset']),
        ];
    }

    /**
     * Gets the public path for the file associated with the uploadable object.
     *
     * @param object      $object    The object
     * @param string|null $fieldName The field name
     * @param string|null $className The object's class. Mandatory if $obj can't be used to determine it
     *
     * @return string|null The public path or null if file not stored
     */
    public function asset($object, ?string $fieldName = null, ?string $className = null): ?string
    {
        if (!$this->helper instanceof UploaderHelper) {
            if (!\is_object($object)) {
                $msg = 'Not passing an object option is deprecated and will be removed in version 2.';
                @\trigger_error($msg, \E_USER_DEPRECATED);
            }
            if (\func_num_args() > 2) {
                $msg = 'Passing a classname is deprecated and will be removed in version 2.';
                @\trigger_error($msg, \E_USER_DEPRECATED);
            }
        }

        // @phpstan-ignore-next-line
        return $this->helper->asset($object, $fieldName, $className);
    }
}
