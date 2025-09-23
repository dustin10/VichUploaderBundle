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

    protected bool $keepExtension = false;

    /**
     * Injects configuration options.
     *
     * @param array $options Options for this namer. The following options are accepted:
     *                       - length: the length of the resulting name.
     *                       - keep_extension: whether to keep the original extension or use smart logic
     */
    public function configure(array $options): void
    {
        if (isset($options['length'])) {
            $this->length = $options['length'];
        }
        if (isset($options['keep_extension'])) {
            $this->keepExtension = $options['keep_extension'];
        }
    }

    public function name(object|array $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        $name = '';
        for ($i = 0; $i < $this->length; ++$i) {
            $name .= $this->getRandomChar();
        }

        if ($extension = $this->getExtensionWithOption($file, $this->keepExtension)) {
            $name = "$name.$extension";
        }

        return $name;
    }

    protected function getRandomChar(): string
    {
        return self::ALPHABET[\random_int(0, 63)];
    }
}
