<?php

namespace Vich\UploaderBundle\Tests\Stub;

/**
 * Test interface for mocking Doctrine QueryBuilder objects.
 */
interface TestQueryBuilderInterface
{
    public function select(mixed ...$select): self;

    public function setFirstResult(?int $firstResult): self;

    public function setMaxResults(?int $maxResults): self;

    public function getQuery(): TestQueryInterface;
}
