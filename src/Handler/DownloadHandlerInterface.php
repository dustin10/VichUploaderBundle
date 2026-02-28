<?php

declare(strict_types=1);

namespace Vich\UploaderBundle\Handler;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface DownloadHandlerInterface
{
    public function downloadObject(object|array $object, string $field, ?string $className = null, string|bool|null $fileName = null, bool $forceDownload = true): StreamedResponse;
}
