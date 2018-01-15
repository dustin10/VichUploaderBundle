<?php

namespace Vich\UploaderBundle\Util;

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
     * @param string $filename
     *
     * @return array An array of basename and extension
     */
    public static function splitNameByExtension(string $filename): array
    {
        $pathInfo = pathinfo($filename);

        return [$pathInfo['filename'], $pathInfo['extension'] ?? ''];
    }
}
