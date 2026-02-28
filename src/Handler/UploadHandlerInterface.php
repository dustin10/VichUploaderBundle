<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Handler;

interface UploadHandlerInterface
{
    public function upload(object $obj, string $fieldName): void;

    public function inject(object $obj, string $fieldName): void;

    public function clean(object $obj, string $fieldName): void;

    public function remove(object $obj, string $fieldName): void;
}
