<?php

namespace Vich\UploaderBundle\Exception;

final class MissingPackageException extends \RuntimeException implements VichUploaderExceptionInterface
{
    public function __construct(string $message = '', \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
