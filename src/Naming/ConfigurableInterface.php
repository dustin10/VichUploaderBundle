<?php

namespace Vich\UploaderBundle\Naming;

/**
 * ConfigurableInterface.
 *
 * Allows namers to receive configuration options.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
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
