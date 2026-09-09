<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

/**
 * Directory namer that chains multiple directory namers together.
 *
 * @author Guillaume Sainthillier <guillaume@silarhi.fr>
 */
final class ChainDirectoryNamer implements DirectoryNamerInterface, ConfigurableInterface, ImmutableConfigurableInterface
{
    /** @var array<DirectoryNamerInterface> */
    private array $namers = [];

    private string $separator = '/';

    public function withOptions(array $options): static
    {
        $chain = clone $this;
        $chain->configure($options);

        // Every child is copied, wherever it comes from: withOptions() is public API and the
        // children it receives may be shared services, not copies made by the resolver. Children
        // implementing only the deprecated ConfigurableInterface have no copy contract and stay shared.
        foreach ($chain->namers as $index => $namer) {
            if (!$namer instanceof ImmutableConfigurableInterface) {
                continue;
            }
            $configured = $namer->withOptions([]);
            if ($configured === $namer) {
                throw new \LogicException(\sprintf('Namer "%s" must return a new instance from withOptions().', $namer::class));
            }
            $chain->namers[$index] = $configured;
        }

        return $chain;
    }

    /**
     * @param array<DirectoryNamerInterface> $namers
     */
    public function setNamers(array $namers): void
    {
        foreach ($namers as $namer) {
            if (!$namer instanceof DirectoryNamerInterface) {
                throw new \InvalidArgumentException('Chained namers must implement DirectoryNamerInterface.');
            }
        }
        $this->namers = $namers;
    }

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - namers: resolved directory namer instances
     *                       - separator: the separator between directory names (default: '/')
     */
    public function configure(array $options): void
    {
        if (\array_key_exists('namers', $options)) {
            $this->setNamers($options['namers']);
        }
        if (isset($options['separator'])) {
            $this->separator = (string) $options['separator'];
        }
    }

    public function directoryName(object|array $object, PropertyMappingInterface $mapping): string
    {
        $directories = [];
        foreach ($this->namers as $namer) {
            $directory = $namer->directoryName($object, $mapping);
            if ('' !== $directory) {
                $directories[] = $directory;
            }
        }

        return \implode($this->separator, $directories);
    }
}
