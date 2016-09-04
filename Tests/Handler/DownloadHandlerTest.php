<?php

namespace Vich\UploaderBundle\Tests\Handler;

use Vich\UploaderBundle\Handler\DownloadHandler;
use Vich\UploaderBundle\Tests\DummyEntity;
use Vich\UploaderBundle\Tests\TestCase;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class DownloadHandlerTest extends TestCase
{
    protected $factory;
    protected $storage;
    protected $object;
    protected $handler;
    protected $mapping;

    public function setUp()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->storage = $this->getMock('Vich\UploaderBundle\Storage\StorageInterface');
        $this->mapping = $this->getPropertyMappingMock();
        $this->object = new DummyEntity();

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
            ['file_name', 'file_name'],
            ['file_name.ext', 'file_name.ext'],
            ['file-name.ext', 'file-name.ext'],
            ['ÉÁŰÚŐPÓÜÉŰÍÍÍÍ$$$$$$$++4334', 'EAUUOPOUEUIIII-4334'],
        ];
    }

    /**
     * @dataProvider filenamesProvider
     */
    public function testDownloadObject($fileName, $expectedFileName)
    {
        $this->mapping
            ->expects($this->once())
            ->method('getFileName')
            ->with($this->object)
            ->will($this->returnValue($fileName));

        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue('something not null'));

        $response = $this->handler->downloadObject($this->object, 'file_field');

        $this->assertInstanceof('\Symfony\Component\HttpFoundation\StreamedResponse', $response);
        $this->assertSame(sprintf('attachment; filename="%s"', $expectedFileName), $response->headers->get('Content-Disposition'));
    }

    public function testNonAsciiFilenameIsTransliterated()
    {
        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue('something not null'));

        $response = $this->handler->downloadObject($this->object, 'file_field', null, 'ÉÁŰÚŐPÓÜÉŰÍÍÍÍ$$$$$$$++4334');

        $this->assertInstanceof('\Symfony\Component\HttpFoundation\StreamedResponse', $response);
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function testAnExceptionIsThrownIfMappingIsntFound()
    {
        $this->factory = $this->getPropertyMappingFactoryMock();
        $this->handler = new DownloadHandler($this->factory, $this->storage);

        $this->handler->downloadObject($this->object, 'file_field');
    }

    /**
     * @expectedException \Vich\UploaderBundle\Exception\NoFileFoundException
     */
    public function testAnExceptionIsThrownIfNoFileIsFould()
    {
        $this->storage
            ->expects($this->once())
            ->method('resolveStream')
            ->with($this->object, 'file_field')
            ->will($this->returnValue(null));

        $this->handler->downloadObject($this->object, 'file_field');
    }
}
