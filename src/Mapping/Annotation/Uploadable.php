<?php

namespace Vich\UploaderBundle\Mapping\Annotation;

use Vich\UploaderBundle\Mapping\AnnotationInterface;

/**
 * @deprecated since 2.9, use Vich\UploaderBundle\Mapping\Attribute\Uploadable instead.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Uploadable implements AnnotationInterface
{
}
