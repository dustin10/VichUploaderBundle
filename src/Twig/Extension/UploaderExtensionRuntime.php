<?php

namespace Vich\UploaderBundle\Twig\Extension;

use Twig\Extension\RuntimeExtensionInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

/**
 * @author Massimiliano Arione <garakkio@gmail.com>
 */
final class UploaderExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var UploaderHelperInterface
     */
    private $helper;

    public function __construct(UploaderHelperInterface $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Gets the public path for the file associated with the uploadable object.
     *
     * @param object|array $object    The object or array
     * @param string|null  $fieldName The field name
     * @param string|null  $className The class name with the uploadable field. Mandatory if $object is an array
     *
     * @return string|null The public path or null if file not stored
     */
    public function asset($object, ?string $fieldName = null, ?string $className = null): ?string
    {
        if (null === $className) {
            return $this->helper->asset($object, $fieldName);
        }

        // @phpstan-ignore-next-line
        return $this->helper->asset($object, $fieldName, $className);
    }
}
