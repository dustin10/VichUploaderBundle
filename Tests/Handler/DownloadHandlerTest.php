<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\TestBundle\Entity\Product;
use Vich\UploaderBundle\Handler\DownloadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class DownloadHandlerTest extends TestCase
{
    protected $factory;
    protected $storage;
    /**
     * @var Product
     */
    protected $object;
    /**
     * @var DownloadHandler
     */
    protected $handler;
    protected $mapping;

    protected function setUp()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->createMock(StorageInterface::class);
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new Product();

        $this->handler = new DownloadHandler($this->factory, $this->storage);
        $this->factory
            ->expects($this->any())
            ->method('fromField')
            ->with($this->object, 'file_field')
            ->will($this->returnValue($this->mapping));
    }

    public function filenamesProvider()
    {
        return [
            ['file_name', 'file-name'],
            ['file_name.ext', 'file-name.ext'],
            ['file-name.ext', 'file-name.ext'],
            ['ÉÁŰÚŐPÓÜÉŰÍÍÍÍ$$$$$$$++4334', 'eauuopoueuiiii-4334'],
        ];
    }

    /**
     * @dataProvider filenamesProvider
     */
    public function testDownloadObject($fileName, $expectedFileName)
    {
        $file = $this->getUploadedFileMock();

        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue($fileName));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue('something not null'));

        $response = $this->handler->downloadObject($this->object, 'file_field');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(sprintf('attachment; filename="%s"', $expectedFileName), $response->headers->get('Content-Disposition'));
    }

    /**
     * @dataProvider filenamesProvider
     */
    public function testDownloadObjectWithoutFile($fileName, $expectedFileName)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue($fileName));

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue('something not null'));

        $response = $this->handler->downloadObject($this->object, 'file_field');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(sprintf('attachment; filename="%s"', $expectedFileName), $response->headers->get('Content-Disposition'));
    }

    public function testDownloadObjectCallOriginalName()
    {
        $this->object->setImageOriginalName('original-name.jpeg');

        $this->mapping
            ->expects($this->once())
            ->method('readProperty')
            ->with($this->object, 'originalName')
            ->will($this->returnValue($this->object->getImageOriginalName()));

        $file = $this->getUploadedFileMock();

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue('something not null'));

        $response = $this->handler->downloadObject($this->object, 'file_field', null, true);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(
            sprintf('attachment; filename="%s"', $this->object->getImageOriginalName()),
            $response->headers->get('Content-Disposition')
        );
    }

    public function testNonAsciiFilenameIsTransliterated()
    {
        $file = $this->getUploadedFileMock();

        $this->mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->will($this->returnValue($file));

        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue(null));

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue('something not null'));

        $response = $this->handler->downloadObject($this->object, 'file_field', null, 'ÉÁŰÚŐPÓÜÉŰÍÍÍÍ$$$$$$$++4334º');

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function testAnExceptionIsThrownIfMappingIsNotFound()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->handler = new DownloadHandler($this->factory, $this->storage);

        $this->handler->downloadObject($this->object, 'file_field');
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\NoFileFoundException
     */
    public function testAnExceptionIsThrownIfNoFileIsFound()
    {
        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue(null));

        $this->handler->downloadObject($this->object, 'file_field');
    }
}
