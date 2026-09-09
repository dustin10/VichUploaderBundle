<?php

namespace Vich\UploaderBundle\Tests\Fixtures;

final class IsolatedNamerFactory
{
    private int $calls = 0;

    public function __construct(private readonly IsolatedNamer $namer)
    {
    }

    public function createNext(): IsolatedNamer
    {
        return $this->namer->withOptions(['prefix' => (string) ++$this->calls]);
    }

    public function create(): IsolatedNamer
    {
        return $this->namer;
    }
}
