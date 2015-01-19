<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download handler.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class DownloadHandler extends AbstractHandler
{
    public function downloadObject($object, $field, $className = null, $fileName = null)
    {
        $mapping = $this->getMapping($object, $field, $className);
        $stream = $this->storage->resolveStream($object, $field, $className);

        return $this->createDownloadResponse(
            $stream,
            $fileName ?: $mapping->getFileName($object)
        );
    }

    private function createDownloadResponse($stream, $filename)
    {
        $response = new StreamedResponse(function () use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 1024);
            }
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/octet-stream');

        return $response;
    }
}
