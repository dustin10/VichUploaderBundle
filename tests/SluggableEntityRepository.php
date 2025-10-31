<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ORM\EntityRepository;

class SluggableEntityRepository extends EntityRepository
{
    public function findOneBySlug(string $slug): ?object
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
