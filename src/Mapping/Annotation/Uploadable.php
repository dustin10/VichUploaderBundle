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
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Uploadable implements AnnotationInterface
{
}
