<?php

namespace Vich\UploaderBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @final
 */
class FileTransformer implements DataTransformerInterface
{
    /**
     * @param UploadedFile $file
     *
     * @return array<string, UploadedFile>
     */
    public function transform($file): array
    {
        return [
            'file' => $file,
        ];
    }

    /**
     * @param array<string, UploadedFile> $data
     *
     * @return UploadedFile
     */
    public function reverseTransform($data)
    {
        return $data['file'];
    }
}
