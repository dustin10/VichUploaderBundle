<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Naming\CurrentDateTimeDirectoryNamer;
use Vich\UploaderBundle\Naming\CurrentDateTimeHelper;
use Vich\UploaderBundle\Naming\DateTimeHelper;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Vyacheslav Startsev <vyacheslav.startsev@gmail.com>
 */
class CurrentDateTimeDirectoryNamerTest extends TestCase
{
    public function directoryNameDataProvider(): array
    {
        return [
            [1537706096, null, '2018/09/23'],
            [1537706096, 'Y/m/d', '2018/09/23'],
            [1537706096, 'Y/d/m', '2018/23/09'],
            [1537706096, 'Y/d/m/H/i/s', '2018/23/09/12/34/56'],
            [1537675268, 'Y/d/m/H/i/s', '2018/23/09/04/01/08'],
            [1537675268, 'y/d/m/G/i/s', '18/23/09/4/01/08'],
        ];
    }

    /**
     * @dataProvider directoryNameDataProvider
     *
     * @param int         $timestamp
     * @param string|null $dateTimeFormat
     * @param string      $expectedName
     */
    public function testNameReturnsTheRightName(int $timestamp, ?string $dateTimeFormat, string $expectedName): void
    {
        \date_default_timezone_set('UTC');
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $dateTimeHelperMock = $this->getMockBuilder(DateTimeHelper::class)
            ->setMethods(['getTimestamp'])
            ->getMock();

        $dateTimeHelperMock
            ->expects($this->once())
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $namer = new CurrentDateTimeDirectoryNamer($dateTimeHelperMock, null);

        if (null !== $dateTimeFormat) {
            $namer->configure(['date_time_format' => $dateTimeFormat]);
        }

        $this->assertSame($expectedName, $namer->directoryName($entity, $mapping));
    }

    public function testConfigurationFailsIfTheDateFormatIsEmpty(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "date_time_format" is empty.');

        $mapping = $this->getPropertyMappingMock();
        $namer = new CurrentDateTimeDirectoryNamer(new CurrentDateTimeHelper(), null);

        $namer->configure(['date_time_format' => '']);

        $namer->directoryName(new DummyEntity(), $mapping);
    }

    public function testNameReturnsObjectDate(): void
    {
        $mapping = $this->getPropertyMappingMock();
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects($this->once())->method('getValue')->willReturn(new \DateTime('2018/12/01'));

        $namer = new CurrentDateTimeDirectoryNamer(new CurrentDateTimeHelper(), $propertyAccessor);
        $namer->configure(['date_time_property' => 'getUploadTimestamp']);

        $name = $namer->directoryName(new DummyEntity(), $mapping);

        $this->assertEquals('2018/12/01', $name);
    }
}
