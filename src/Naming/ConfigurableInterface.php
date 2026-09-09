<?php

namespace Vich\UploaderBundle\Naming;

/**
 * ConfigurableInterface.
 *
 * Allows namers to receive configuration options.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 *
 * @deprecated since 3.1, use {@see ImmutableConfigurableInterface} instead: configuring a shared
 *             namer service in place gives every mapping the options of the last one resolved.
 */
interface ConfigurableInterface
{
    /**
     * Injects configuration options.
     *
     * @param array $options The options
     */
    public function configure(array $options): void;
}
