<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer wich uses hash function from random string for generating names.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class HashNamer implements NamerInterface, ConfigurableInterface
{
    use Polyfill\FileExtensionTrait;

    /** @var string */
    private $algorithm = 'sha1';

    /** @var int */
    private $length;

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - algorithm: wich hash algorithm to use.
     *                       - length: limit file name length
     */
    public function configure(array $options): void
    {
        $options = \array_merge(['algorithm' => $this->algorithm, 'length' => $this->length], $options);

        $this->algorithm = $options['algorithm'];
        $this->length = $options['length'];
    }

    public function name(object $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        $name = \hash($this->algorithm, $this->getRandomString());
        if (null !== $this->length) {
            $name = \substr($name, 0, $this->length);
        }

        if ($extension = $this->getExtension($file)) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }

    protected function getRandomString(): string
    {
        return \microtime(true).\random_int(0, 9_999_999);
    }
}
