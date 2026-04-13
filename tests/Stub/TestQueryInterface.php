<?php

namespace Vich\UploaderBundle\Tests\Stub;

/**
 * Test interface for mocking Doctrine Query objects.
 */
interface TestQueryInterface
{
    public function getSingleScalarResult(): mixed;

    /**
     * @return array<mixed>
     */
    public function getResult(): array;
}
