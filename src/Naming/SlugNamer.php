<?php

namespace Vich\UploaderBundle\Naming;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Util\Transliterator;

/**
 * This namer uses a slug to keep original name when possibile.
 *
 * @author Massimiliano Arione <garakkio@gmail.com>
 */
final class SlugNamer implements NamerInterface
{
    /**
     * @var Transliterator
     */
    private $transliterator;

    /**
     * @var object
     */
    private $service;

    /**
     * @var string
     */
    private $method;

    public function __construct(Transliterator $transliterator, object $service, string $method)
    {
        $this->transliterator = $transliterator;
        $this->service = $service;
        $this->method = $method;
    }

    public function name($object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $extension = \strtolower(\pathinfo($originalName, \PATHINFO_EXTENSION));
        $basename = \substr(\pathinfo($originalName, \PATHINFO_FILENAME), 0, 240);
        $basename = \strtolower($this->transliterator->transliterate($basename));
        $slug = \sprintf('%s.%s', $basename, $extension);

        // check if there another object with same slug
        $num = 0;
        while (true) {
            $otherObject = $this->service->{$this->method}($slug);
            if (null === $otherObject) {
                return $slug;
            }
            $slug = \sprintf('%s-%d.%s', $basename, ++$num, $extension);
        }
    }
}
