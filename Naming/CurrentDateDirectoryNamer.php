<?php

namespace Vich\UploaderBundle\Naming;

/**
 * CurrentDateDirectoryNamer
 *
 * @author David Romaní <david@flux.cat>
 */
class CurrentDateDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * Get current date directory name with format yyyy/mm/dd
     *
     * @param  object $obj       The object the upload is attached to.
     * @param  string $field     The name of the uploadable field to generate a name for.
     * @param  string $uploadDir The upload directory set in config
     *
     * @return string The directory name
     */
    public function directoryName($obj, $field, $uploadDir)
    {
        $currentDate = new \DateTime();

        return $uploadDir . DIRECTORY_SEPARATOR . $currentDate->format('Y') . DIRECTORY_SEPARATOR . $currentDate->format('m') . DIRECTORY_SEPARATOR . $currentDate->format('d');
    }
}