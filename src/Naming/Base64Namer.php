<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * Namer using a random base64 string. The resulting name will contain lower- and uppercase alphanumeric
 * characters, '-' and '_', so it can be safely used in URLs.
 *
 * @author Keleti Márton <tejes@hac.hu>
 */
class Base64Namer implements NamerInterface, ConfigurableInterface
{
    use Polyfill\FileExtensionTrait;

    protected const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

    /** @var int Length of the resulting name. 10 can be decoded to a 64-bit integer. */
    protected $length = 10;

    /**
     * Injects configuration options.
     *
     * @param array $options Options for this namer. The following options are accepted:
     *                       - length: the length of the resulting name.
     */
    public function configure(array $options): void
    {
        if (isset($options['length'])) {
            $this->length = $options['length'];
        }
    }

    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        $name = '';
        for ($i = 0; $i < $this->length; ++$i) {
            $name .= $this->getRandomChar();
        }

        if ($extension = $this->getExtension($file)) {
            $name = "$name.$extension";
        }

        return $name;
    }

    protected function getRandomChar(): string
    {
        return self::ALPHABET[\random_int(0, 63)];
    }
}
