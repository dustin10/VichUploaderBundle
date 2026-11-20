<?php

namespace Vich\UploaderBundle\Handler;

interface DeferredCleanHandlerInterface
{
    /**
     * Dispatches the pre remove event and erases the file fields, but leaves the file in the storage.
     *
     * Returns null when no uploaded file replaces an existing one, or when the removal was canceled.
     */
    public function prepareClean(object $obj, string $fieldName): ?PendingClean;

    public function completeClean(PendingClean $pendingClean): void;
}
