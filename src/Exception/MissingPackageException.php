<?php

namespace Vich\UploaderBundle\Exception;

class MissingPackageException extends \RuntimeException
{
    public function __construct($message = '', \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
