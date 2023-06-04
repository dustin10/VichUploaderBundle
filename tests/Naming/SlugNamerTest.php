<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Doctrine\ORM\EntityRepository;
use Vich\UploaderBundle\Naming\SlugNamer;
use Vich\UploaderBundle\Tests\TestCase;

final class SlugNamerTest extends TestCase
{
    public static function fileDataProvider(): array
    {
        return [
            // case -> original name, result pattern
            'non existing' => ['lala.jpeg', '/lala.jpeg/'],
            'existing' => ['làlà.mp3', '/lala-1.mp3/'],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsAnUniqueName(string $originalName, string $pattern): void
    {
        $file = $this->getUploadedFileMock();
        $file
            ->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn($originalName)
        ;

        $entity = new \stdClass();

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects(self::once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file)
        ;

        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findOneBySlug'])
            ->getMock()
        ;
        $repo
            ->method('findOneBySlug')
            ->willReturnMap([['lala.jpeg', null], ['lala.mp3', new \stdClass()]])
        ;

        $namer = new SlugNamer($this->getTransliterator(), $repo, 'findOneBySlug');

        self::assertMatchesRegularExpression($pattern, $namer->name($entity, $mapping));
    }
}
