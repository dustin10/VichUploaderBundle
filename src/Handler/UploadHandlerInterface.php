<?php

namespace Vich\UploaderBundle\Handler;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
interface UploadHandlerInterface
{
    /**
     * Checks for file to upload.
     *
     * @param object $obj       The object
     * @param string $fieldName The name of the field containing the upload (has to be mapped)
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function upload(object $obj, string $fieldName): void;

    /**
     * Injects the file for the given field.
     *
     * @param object $obj       The object
     * @param string $fieldName The name of the field containing the upload (has to be mapped)
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function inject(object $obj, string $fieldName): void;

    /**
     * Cleans the file for the given field (removes any uploaded file before upload).
     *
     * @param object $obj       The object
     * @param string $fieldName The name of the field containing the upload (has to be mapped)
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function clean(object $obj, string $fieldName): void;

    /**
     * Removes the file for the given field.
     *
     * @param object $obj       The object
     * @param string $fieldName The name of the field containing the upload (has to be mapped)
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function remove(object $obj, string $fieldName): void;
}
