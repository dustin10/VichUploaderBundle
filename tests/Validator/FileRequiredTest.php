<?php

namespace Vich\UploaderBundle\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Vich\UploaderBundle\Validator\Constraints\FileRequired;

final class FileRequiredTest extends TestCase
{
    public function testTargetOption(): void
    {
        $constraint = new FileRequired(target: 'image');

        $this->assertSame('image', $constraint->target);
    }

    /**
     * @legacy
     */
    public function testTargetOptionWithArray(): void
    {
        if (Kernel::VERSION_ID >= 70400) {  // @phpstan-ignore-line greaterOrEqual.alwaysTrue
            self::markTestSkipped('Passing options as an array is deprecated in Symfony 7.3 and removed in 8.0.');
        }
        // @phpstan-ignore-next-line deadCode.unreachable
        $constraint = new FileRequired(['target' => 'document']);

        $this->assertSame('document', $constraint->target);
    }

    public function testNoTargetOption(): void
    {
        $constraint = new FileRequired();

        $this->assertNull($constraint->target);
    }

    public function testMessageOption(): void
    {
        $message = 'Custom file required message';
        $constraint = new FileRequired(message: $message, target: 'file');

        $this->assertSame($message, $constraint->message);
    }

    public function testGroupsOption(): void
    {
        $groups = ['upload', 'validation'];
        $constraint = new FileRequired(groups: $groups, target: 'file');

        $this->assertSame($groups, $constraint->groups);
    }

    public function testAllowNullOption(): void
    {
        $constraint = new FileRequired(allowNull: true, target: 'file');

        $this->assertTrue($constraint->allowNull);
    }

    public function testDefaultOptions(): void
    {
        $constraint = new FileRequired(target: 'file');

        $this->assertFalse($constraint->allowNull);
        $this->assertNull($constraint->normalizer);
        $this->assertSame(['Default'], $constraint->groups);
    }
}
