<?php

namespace Vich\UploaderBundle\Mapping\Attribute;

use Vich\UploaderBundle\Mapping\AttributeInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Uploadable implements AttributeInterface
{
}
