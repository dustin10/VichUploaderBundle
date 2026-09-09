<?php

namespace Vich\UploaderBundle\Naming;

/**
 * Implements configuration copies for namers whose mutable state can be safely cloned.
 *
 * Namers holding mutable configuration objects must implement __clone() to copy that state,
 * or implement withOptions() themselves. Read-only dependencies can remain shared.
 */
trait ConfigurableNamerTrait
{
    public function withOptions(array $options): static
    {
        $namer = clone $this;
        if ([] !== $options) {
            $namer->configure($options);
        }

        return $namer;
    }

    abstract public function configure(array $options): void;
}
