<?php

namespace Vich\UploaderBundle\Tests\Handler;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Exception\MappingNotFoundException;
use Vich\UploaderBundle\Exception\NoFileFoundException;
use Vich\UploaderBundle\Handler\DownloadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
#[AllowMockObjectsWithoutExpectations]
final class DownloadHandlerTest extends TestCase
{
    protected PropertyMappingFactoryInterface&Stub $factory;

    protected StorageInterface&MockObject $storage;

    protected Product $object;

    protected DownloadHandler $handler;

    protected PropertyMappingInterface&MockObject $mapping;

    protected function setUp(): void
    {
        $this->factory = $this->getPropertyMappingFactoryStub();
        $this->storage = $this->createMock(StorageInterface::class);
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new Product();

        $this->handler = new DownloadHandler($this->factory, $this->storage);
        $this->factory
            ->method('fromField')
            ->willReturnMap([[$this->object, 'file_field', null, $this->mapping]]);
    }

    public static function filenamesProvider(): array
    {
        return [
            ['file name', 'file name', null],
            ['file_name', 'file_name', null],
            ['file_name.ext', 'file_name.ext', null],
            ['file-name.ext', 'file-name.ext', null],
            ['file-name-/\-ẞ-ćź-ů-💩-%.ext', 'file-name--%E1%BA%9E-%C4%87%C5%BA-%C5%AF-%F0%9F%92%A9-.ext', 'file-name------.ext'],
        ];
    }

    #[DataProvider('filenamesProvider')]
    #[Test]
    public function downloadObject(string $fileName, string $expectedFileName, ?string $expectedFallbackFilename): void
    {
        $file = $this->getUploadedFileMock();

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn($fileName);

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($file);

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn(null);

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->willReturn('something not null');

        $response = $this->handler->downloadObject($this->object, 'file_field');

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertMatchesRegularExpression(
            $this->getDispositionHeaderRegexp('attachment', $expectedFileName, $expectedFallbackFilename),
            $response->headers->get('Content-Disposition')
        );
    }

    #[DataProvider('filenamesProvider')]
    #[Test]
    public function displayObject(string $fileName, string $expectedFileName, ?string $expectedFallbackFilename): void
    {
        $file = $this->getUploadedFileMock();

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn($fileName);

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($file);

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('application/pdf');

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->willReturn('something not null');

        $response = $this->handler->downloadObject($this->object, 'file_field', null, null, false);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertMatchesRegularExpression(
            $this->getDispositionHeaderRegexp('inline', $expectedFileName, $expectedFallbackFilename),
            $response->headers->get('Content-Disposition')
        );
    }

    #[DataProvider('filenamesProvider')]
    #[Test]
    public function downloadObjectWithoutFile(string $fileName, string $expectedFileName, ?string $expectedFallbackFilename): void
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->willReturn($fileName);

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn(null);

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->willReturn('something not null');

        $response = $this->handler->downloadObject($this->object, 'file_field');

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertMatchesRegularExpression(
            $this->getDispositionHeaderRegexp('attachment', $expectedFileName, $expectedFallbackFilename),
            $response->headers->get('Content-Disposition')
        );
    }

    #[Test]
    public function downloadObjectCallOriginalName(): void
    {
        $this->object->setImageOriginalName('original-name.jpeg');

        $this->mapping
            ->expects($this->exactly(2))
            ->method('readProperty')
            ->willReturnMap([
                [$this->object, 'originalName', $this->object->getImageOriginalName()],
                [$this->object, 'mimeType', $this->object->getImageMimeType()],
            ]);

        $file = $this->getUploadedFileMock();

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn($file);

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('application/octet-stream');

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->willReturn('something not null');

        $response = $this->handler->downloadObject($this->object, 'file_field', null, true);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertMatchesRegularExpression(
            $this->getDispositionHeaderRegexp('attachment', $this->object->getImageOriginalName()),
            $response->headers->get('Content-Disposition')
        );
    }

    #[Test]
    public function anExceptionIsThrownIfMappingIsNotFound(): void
    {
        $this->expectException(MappingNotFoundException::class);

        $this->factory = $this->getPropertyMappingFactoryStub();
        $this->handler = new DownloadHandler($this->factory, $this->storage);

        $this->handler->downloadObject($this->object, 'file_field');
    }

    #[Test]
    public function anExceptionIsThrownIfNoFileIsFound(): void
    {
        $this->expectException(NoFileFoundException::class);

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->willReturn(null);

        $this->handler->downloadObject($this->object, 'file_field');
    }

    private function getDispositionHeaderRegexp(string $disposition, string $expectedFileName, ?string $expectedFallbackFilename = null): string
    {
        if (null !== $expectedFallbackFilename) {
            return \sprintf('/%s; filename=["]{0,1}%s["]{0,1}; filename\*=utf-8\'\'%s/', $disposition, $expectedFallbackFilename, $expectedFileName);
        }

        return \sprintf('/%s; filename=["]{0,1}%s["]{0,1}/', $disposition, $expectedFileName);
    }
}
