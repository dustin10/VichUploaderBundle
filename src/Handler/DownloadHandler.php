<?php

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\UploaderBundle\Exception\NoFileFoundException;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @final
 */
class DownloadHandler extends AbstractHandler
{
    /**
     * Create a response object that will trigger the download of a file.
     *
     * @param object|array $object
     * @param string       $className
     * @param string|true  $fileName  True to return original file name
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     * @throws NoFileFoundException
     * @throws \InvalidArgumentException
     */
    public function downloadObject($object, string $field, ?string $className = null, $fileName = null, bool $forceDownload = true): StreamedResponse
    {
        $mapping = $this->getMapping($object, $field, $className);
        $stream = $this->storage->resolveStream($object, $field, $className);

        if (null === $stream) {
            throw new NoFileFoundException(\sprintf('No file found in field "%s".', $field));
        }

        if (true === $fileName) {
            $fileName = $mapping->readProperty($object, 'originalName');
        }

        $mimeType = $mapping->readProperty($object, 'mimeType');

        if (null === $mimeType) {
            try {
                $file = $mapping->getFile($object);
                if (null !== $file) {
                    $mimeType = $file->getMimeType();
                }
            } catch (FileNotFoundException $exception) {
                $mimeType = null;
            }
        }

        return $this->createDownloadResponse(
            $stream,
            $fileName ?: $mapping->getFileName($object),
            $mimeType,
            $forceDownload
        );
    }

    /**
     * @param resource $stream
     *
     * @throws \InvalidArgumentException
     */
    private function createDownloadResponse($stream, string $filename, ?string $mimeType = 'application/octet-stream', bool $forceDownload = true): StreamedResponse
    {
        $response = new StreamedResponse(static function () use ($stream): void {
            \stream_copy_to_stream($stream, \fopen('php://output', 'wb'));
        });

        $filename = \str_replace(['%', '/', '\\'], '', $filename);

        $disposition = $response->headers->makeDisposition(
            $forceDownload ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            $filename,
            \filter_var($filename, \FILTER_UNSAFE_RAW, \FILTER_FLAG_STRIP_HIGH)
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $mimeType ?: 'application/octet-stream');

        return $response;
    }
}
