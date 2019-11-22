<?php

namespace Vich\UploaderBundle\Tests;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class DummyImageFile
{
}
