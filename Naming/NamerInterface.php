<?php

namespace Vich\UploaderBundle\Naming;

/**
 * NamerInterface.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface NamerInterface
{
    /**
     * Creates a name for the file being uploaded.
     *
     * @param  object $obj   The object the upload is attached to.
     * @param  string $field The name of the uploadable field to generate a name for.
     * @return string The file name.
     */
    public function name($obj, $field);
}
