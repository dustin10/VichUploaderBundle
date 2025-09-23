<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer that uses hash function from random string for generating names.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class HashNamer implements NamerInterface, ConfigurableInterface
{
    use Polyfill\FileExtensionTrait;

    private string $algorithm = 'sha1';

    private ?int $length = null;

    private bool $keepExtension = false;

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - algorithm: which hash algorithm to use.
     *                       - length: limit file name length
     *                       - keep_extension: whether to keep the original extension or use smart logic
     */
    public function configure(array $options): void
    {
        $options = \array_merge(['algorithm' => $this->algorithm, 'length' => $this->length, 'keep_extension' => $this->keepExtension], $options);

        $this->algorithm = $options['algorithm'];
        $this->length = $options['length'];
        $this->keepExtension = $options['keep_extension'];
    }

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        $name = \hash($this->algorithm, $this->getRandomString());
        if (null !== $this->length) {
            $name = \substr($name, 0, $this->length);
        }

        if ($extension = $this->getExtensionWithOption($file, $this->keepExtension)) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }

    protected function getRandomString(): string
    {
        return \microtime(true).\random_int(0, 9_999_999);
    }
}
