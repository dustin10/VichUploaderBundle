<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\UploaderBundle\Exception\NoFileFoundException;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class DownloadHandler extends AbstractHandler
{
    /**
     * Create a response object that will trigger the download of a file.
     *
     * @param object|array $object
     * @param string       $field
     * @param string       $className
     * @param string|true  $fileName  True to return original file name
     *
     * @return StreamedResponse
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     * @throws NoFileFoundException
     * @throws \InvalidArgumentException
     */
    public function downloadObject($object, string $field, ?string $className = null, $fileName = null): StreamedResponse
    {
        $mapping = $this->getMapping($object, $field, $className);
        $stream = $this->storage->resolveStream($object, $field, $className);

        if (null === $stream) {
            throw new NoFileFoundException(sprintf('No file found in field "%s".', $field));
        }

        if (true === $fileName) {
            $fileName = $mapping->readProperty($object, 'originalName');
        }

        $file = $mapping->getFile($object);

        return $this->createDownloadResponse(
            $stream,
            $fileName ?: $mapping->getFileName($object),
            null === $file ? null : $file->getMimeType()
        );
    }

    /**
     * @param resource    $stream
     * @param string      $filename
     * @param string|null $mimeType
     *
     * @return StreamedResponse
     *
     * @throws \InvalidArgumentException
     */
    private function createDownloadResponse($stream, string $filename, ?string $mimeType = 'application/octet-stream'): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($stream): void {
            stream_copy_to_stream($stream, fopen('php://output', 'wb'));
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Transliterator::transliterate($filename)
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $mimeType ?: 'application/octet-stream');

        return $response;
    }
}
