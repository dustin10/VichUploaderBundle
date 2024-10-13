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
    use Polyfill\FileExtensionTrait;

    public function __construct(private readonly Transliterator $transliterator, private readonly object $service, private readonly string $method)
    {
    }

    public function name(object $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);
        $originalName = $file->getClientOriginalName();
        $extension = $this->getExtension($file);
        $basename = \substr(\pathinfo($originalName, \PATHINFO_FILENAME), 0, 240);
        $basename = \strtolower($this->transliterator->transliterate($basename));
        $slug = is_string($extension) && '' !== $extension
            ? \sprintf('%s.%s', $basename, $extension)
            : $basename;

        // check if there another object with same slug
        $num = 0;
        while (true) {
            $otherObject = $this->service->{$this->method}($slug);
            if (null === $otherObject) {
                return $slug;
            }

            $slug = \is_string($extension) && '' !== $extension
                ? \sprintf('%s-%d.%s', $basename, ++$num, $extension)
                : \sprintf('%s-%d', $basename, ++$num)
            ;
        }
    }
}
