<?php

namespace Vich\UploaderBundle\Tests\Naming;

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
     * @param int $timestamp
     * @param null|string $dateTimeFormat
     * @param string $expectedName
     */
    public function testNameReturnsTheRightName(int $timestamp, ?string $dateTimeFormat, string $expectedName): void
    {
        $entity = new DummyEntity();
        $mapping = $this->getPropertyMappingMock();

        $dateTimeHelperMock = $this->getMockBuilder(DateTimeHelper::class)
            ->setMethods(['getTimestamp'])
            ->getMock();

        $dateTimeHelperMock
            ->expects($this->once())
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $namer = new CurrentDateTimeDirectoryNamer($dateTimeHelperMock);

        if (!is_null($dateTimeFormat)) {
            $namer->configure(['date_time_format' => $dateTimeFormat]);
        }

        $this->assertSame($expectedName, $namer->directoryName($entity, $mapping));
    }

    public function testConfigurationFailsIfTheDateFormatIsEmpty(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "date_time_format" is empty.');

        $mapping = $this->getPropertyMappingMock();
        $namer = new CurrentDateTimeDirectoryNamer(new CurrentDateTimeHelper());

        $namer->configure(['date_time_format' => '']);

        $namer->directoryName(new DummyEntity(), $mapping);
    }
}
