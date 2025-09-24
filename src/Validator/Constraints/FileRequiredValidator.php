<?php

namespace Vich\UploaderBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlankValidator;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class FileRequiredValidator extends NotBlankValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof FileRequired && $constraint->target) {
            try {
                $targetValue = PropertyAccess::createPropertyAccessor()->getValue(
                    $this->context->getObject(),
                    $constraint->target
                );
            } catch (\Exception $e) {
                // If property access fails, fall back to standard NotBlank validation
                parent::validate($value, $constraint);

                return;
            }

            // Skip validation if target has an existing file with a name
            if ($targetValue instanceof \Vich\UploaderBundle\Entity\File && !empty($targetValue->getName())) {
                return;
            }

            // Skip validation if we have a valid uploaded file (check the actual value being validated)
            if ($value instanceof UploadedFile && $value->isValid()) {
                return;
            }

            // Skip validation if we have a replacing file (for programmatic uploads)
            if ($value instanceof ReplacingFile) {
                return;
            }
        }

        parent::validate($value, $constraint);
    }
}
