<?php

namespace Vich\UploaderBundle\Tests\Naming;

use Vich\UploaderBundle\Naming\HashNamer as BaseHashNamer;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

class HashNamer extends BaseHashNamer
{
    protected function getRandomString(): string
    {
        return 'abcd';
    }
}

/**
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class HashNamerTest extends TestCase
{
    public function fileDataProvider(): array
    {
        return [
            ['81fe8bfe87576c3ecb22426f8e57847382917acf.jpg', 'jpg', 'sha1', null],
            ['81fe8bfe87576c3ecb22426f8e57847382917acf', '', 'sha1', null],
            ['e2fc714c4727ee9395f324cd2e7f331f.jpg', 'jpg', 'md5', null],
            ['81fe8bfe87576c3ecb22.jpg', 'jpg', 'sha1', 20],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     */
    public function testNameReturnsTheRightName($expectedFileName, $extension, $algorithm, $length): void
    {
        $file = $this->getUploadedFileMock();
        $file->expects($this->once())
            ->method('getClientOriginalName');

        $file->expects($this->once())
            ->method('guessExtension')
            ->willReturn($extension);

        $entity = new DummyEntity();
        $entity->setFile($file);

        $mapping = $this->getPropertyMappingMock();
        $mapping->expects($this->once())
            ->method('getFile')
            ->with($entity)
            ->willReturn($file);

        $namer = new HashNamer();
        $namer->configure(['algorithm' => $algorithm, 'length' => $length]);

        $this->assertSame($expectedFileName, $namer->name($entity, $mapping));
    }
}
