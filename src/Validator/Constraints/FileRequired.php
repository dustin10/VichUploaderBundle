<?php

namespace Vich\UploaderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FileRequired extends NotBlank
{
    public const IS_BLANK_ERROR = 'eabafc49-05cb-4863-9609-0323c6899215';

    public mixed $target;

    public function __construct(
        ?array $options = null,
        ?string $message = null,
        ?bool $allowNull = null,
        ?callable $normalizer = null,
        ?array $groups = null,
        mixed $payload = null,
        mixed $target = null,
    ) {
        if (!\class_exists(NotBlank::class)) {
            throw new \LogicException('You cannot use FileRequired as the Validator component is not installed. Try running "composer require symfony/validator".');
        }

        if (\is_array($options)) {
            trigger_deprecation('vich/uploader-bundle', '2.9', 'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.', static::class);
        }

        // Handle options array format
        if (\is_array($options) && \array_key_exists('target', $options)) {
            $target = $options['target'];
            unset($options['target']);
        }

        parent::__construct($options, $message, $allowNull, $normalizer, $groups, $payload);
        $this->target = $target;
    }
}
