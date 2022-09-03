<?php

namespace Vich\UploaderBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileTransformer implements DataTransformerInterface
{
    /**
     * @param UploadedFile $value
     *
     * @return array<string, UploadedFile>
     */
    public function transform($value): array
    {
        return [
            'file' => $value,
        ];
    }

    /**
     * @param array<string, UploadedFile> $value
     */
    public function reverseTransform($value): ?UploadedFile
    {
        return $value['file'];
    }
}
