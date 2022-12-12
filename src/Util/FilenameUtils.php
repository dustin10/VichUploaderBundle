<?php

namespace Vich\UploaderBundle\Util;

use function strrpos;
use function substr;
use function trigger_deprecation;

/**
 * @internal
 */
final class FilenameUtils
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Splits filename for array of basename and extension.
     *
     * @return array An array of basename and extension
     */
    public static function splitNameByExtension(string $filename): array
    {
        if (false === $pos = strrpos($filename, '.')) {
            return [$filename, ''];
        }

        return [substr($filename, 0, $pos), substr($filename, $pos + 1)];
    }

    /**
     * Splits filename for array of basename and extension.
     *
     * @return array An array of basename and extension
     *
     * @deprecated, use splitNameByExtension() instead
     */
    public static function spitNameByExtension(string $filename): array
    {
        trigger_deprecation('vich/uploader-bundle', '2.0.2', '"%s()" is deprecated, use "splitNameByExtension()" instead.', __METHOD__);

        return $this->splitNameByExtension($filename);
    }
}
