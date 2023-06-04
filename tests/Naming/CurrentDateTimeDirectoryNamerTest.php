<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
final class CurrentDateTimeDirectoryNamerTest extends TestCase
{
    public static function directoryNameDataProvider(): array
    {
        return [
            [1_537_706_096, null, '2018/09/23'],
            [1_537_706_096, 'Y/m/d', '2018/09/23'],
            [1_537_706_096, 'Y/d/m', '2018/23/09'],
            [1_537_706_096, 'Y/d/m/H/i/s', '2018/23/09/12/34/56'],
            [1_537_675_268, 'Y/d/m/H/i/s', '2018/23/09/04/01/08'],
            [1_537_675_268, 'y/d/m/G/i/s', '18/23/09/4/01/08'],
        ];
    }

    /**
     * @dataProvider directoryNameDataProvider
     */
    public function testNameReturnsTheRightName(int $timestamp, ?string $dateTimeFormat, string $expectedName): void
    {
        \date_default_timezone_set('UTC');
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->method('getValue')->willReturn(new \DateTime(\date('Y-m-d H:i:s', $timestamp)));

        $namer = new CurrentDateTimeDirectoryNamer($propertyAccessor);
        $namer->configure(['date_time_property' => 'getUploadTimestamp']);

        if (null !== $dateTimeFormat) {
            $namer->configure(['date_time_format' => $dateTimeFormat]);
        }

        self::assertSame($expectedName, $namer->directoryName($entity, $mapping));
    }

    public function testConfigurationFailsIfTheDateFormatIsEmpty(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "date_time_format" is empty.');

        $mapping = $this->getPropertyMappingMock();
        $namer = new CurrentDateTimeDirectoryNamer(null);

        $namer->configure(['date_time_format' => '']);

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    public function testNameReturnsObjectDate(): void
    {
        $mapping = $this->getPropertyMappingMock();
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::once())->method('getValue')->willReturn(new \DateTime('2018/12/01'));

        $namer = new CurrentDateTimeDirectoryNamer($propertyAccessor);
        $namer->configure(['date_time_property' => 'getUploadTimestamp']);

        $name = $namer->directoryName(new DummyEntity(), $mapping);

        self::assertEquals('2018/12/01', $name);
    }
}
