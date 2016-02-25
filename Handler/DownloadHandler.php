<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Vich\UploaderBundle\Exception\NoFileFoundException;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * Download handler.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class DownloadHandler extends AbstractHandler
{
    /**
     * Create a response object that will trigger the download of a file.
     *
     * @param mixed  $object
     * @param string $field
     * @param string $className
     * @param string $fieldName
     *
     * @return StreamedResponse
     */
    public function downloadObject($object, $field, $className = null, $fileName = null)
    {
        $mapping = $this->getMapping($object, $field, $className);
        $stream  = $this->storage->resolveStream($object, $field, $className);

        if ($stream === null) {
            throw new NoFileFoundException(sprintf('No file found in field "%s".', $field));
        }

        return $this->createDownloadResponse(
            $stream,
            $fileName ?: $mapping->getFileName($object)
        );
    }

    private function createDownloadResponse($stream, $filename)
    {
        $response = new StreamedResponse(function () use ($stream) {
            stream_copy_to_stream($stream, fopen('php://output', 'w'));
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Transliterator::transliterate($filename)
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/octet-stream');

        return $response;
    }
}
