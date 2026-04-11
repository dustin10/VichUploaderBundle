<?php

namespace Vich\UploaderBundle\Tests\Stub;

use Doctrine\Persistence\ObjectRepository;

/**
 * Test interface for mocking Doctrine Repository objects.
 */
interface TestRepositoryInterface extends ObjectRepository
{
    public function createQueryBuilder(string $alias): TestQueryBuilderInterface;
}
