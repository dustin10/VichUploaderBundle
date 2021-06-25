<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * Uploadable.
 *
 * @Annotation
 * @Target({"CLASS"})
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Uploadable implements AnnotationInterface
{
}
