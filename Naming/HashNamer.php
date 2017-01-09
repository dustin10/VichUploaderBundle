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
    private $algorithm = 'sha1';
    private $length;

    /**
     * @param array $options Options for this namer. The following options are accepted:
     *                       - algorithm: wich hash algorithm to use.
     *                       - length: limit file name length
     */
    public function configure(array $options)
    {
        $options = array_merge(['algorithm' => $this->algorithm, 'length' => $this->length], $options);

        $this->algorithm = $options['algorithm'];
        $this->length = $options['length'];
    }

    /**
     * {@inheritdoc}
     */
    public function name($object, PropertyMapping $mapping)
    {
        $file = $mapping->getFile($object);

        $name = hash($this->algorithm, $this->getRandomString());
        if (null !== $this->length) {
            $name = substr($name, 0, $this->length);
        }

        if ($extension = $file->guessExtension()) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }

    protected function getRandomString()
    {
        return microtime(true).mt_rand(0, 9999999);
    }
}
