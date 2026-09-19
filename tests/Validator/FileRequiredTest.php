<?php

namespace Vich\UploaderBundle\Tests\Validator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Validator\Constraints\FileRequired;

final class FileRequiredTest extends TestCase
{
    #[Test]
    public function targetOption(): void
    {
        $constraint = new FileRequired(target: 'image');

        $this->assertSame('image', $constraint->target);
    }

    #[Test]
    public function noTargetOption(): void
    {
        $constraint = new FileRequired();

        $this->assertNull($constraint->target);
    }

    #[Test]
    public function messageOption(): void
    {
        $message = 'Custom file required message';
        $constraint = new FileRequired(message: $message, target: 'file');

        $this->assertSame($message, $constraint->message);
    }

    #[Test]
    public function groupsOption(): void
    {
        $groups = ['upload', 'validation'];
        $constraint = new FileRequired(groups: $groups, target: 'file');

        $this->assertSame($groups, $constraint->groups);
    }

    #[Test]
    public function allowNullOption(): void
    {
        $constraint = new FileRequired(allowNull: true, target: 'file');

        $this->assertTrue($constraint->allowNull);
    }

    #[Test]
    public function defaultOptions(): void
    {
        $constraint = new FileRequired(target: 'file');

        $this->assertFalse($constraint->allowNull);
        $this->assertNull($constraint->normalizer);
        $this->assertSame(['Default'], $constraint->groups);
    }
}
