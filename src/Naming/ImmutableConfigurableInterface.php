<?php

namespace Vich\UploaderBundle\Naming;

/**
 * Allows namers to receive per-mapping configuration options without shared mutable state.
 *
 * Namer services are shared: implement this interface to hand out an independent copy per mapping.
 */
interface ImmutableConfigurableInterface
{
    /**
     * Returns a new namer with independent mutable configuration, preserving service defaults.
     *
     * Neither this instance nor previously returned instances may be modified. This also applies
     * to mutable configuration stored in dependencies, decorators and nested namers. An empty
     * array must still return an independent instance with the current configuration.
     *
     * @param array<string, mixed> $options
     */
    public function withOptions(array $options): static;
}
