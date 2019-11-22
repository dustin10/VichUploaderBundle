<?php

namespace Vich\UploaderBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class FileTransformer implements DataTransformerInterface
{
    public function transform($file): array
    {
        return [
            'file' => $file,
        ];
    }

    public function reverseTransform($data)
    {
        return $data['file'];
    }
}
