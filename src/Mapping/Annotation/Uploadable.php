<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * Uploadable.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @NamedArgumentConstructor
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 * @final
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Uploadable implements AnnotationInterface
{
}
