<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

final class NamerFactory
{
    public function __construct(private readonly NonCloneableNamer $namer)
    {
    }

    public function create(): NonCloneableNamer
    {
        return $this->namer;
    }
}
