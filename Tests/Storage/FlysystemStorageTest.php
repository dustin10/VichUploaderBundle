<?php

namespace Vich\UploaderBundle\Tests\Storage;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Tests\DummyEntity;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FlysystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var FlysystemStorage
     */
    private $storage;

    protected function setUp()
    {
        $this->root = sys_get_temp_dir().'/test';

        $this->fs = new Filesystem(new Local($this->root));

        $this->factory = $this->getMockBuilder('Vich\\UploaderBundle\\Mapping\\PropertyMappingFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage = new FlysystemStorage($this->fs, $this->factory);
    }

    protected function tearDown()
    {
        $it = new \FilesystemIterator($this->root);
        foreach ($it as $path) {
            @unlink($path->getRealPath());
        }
    }

    public function testUpload()
    {
        $mapping = new PropertyMapping('file', 'fileName');

        $this->factory->expects($this->once())
            ->method('fromObject')
            ->will($this->returnValue(array($mapping)));

        $filename = tempnam(sys_get_temp_dir(), 'uploader');
        file_put_contents($filename, 'Hello World!');

        $obj = new DummyEntity();
        $obj->setFileName('test.txt');
        $obj->setFile(new UploadedFile($filename, 'test.txt', null, null, null, true));

        $this->storage->upload($obj);

        $this->assertTrue(is_file($this->root.'/test.txt'));
    }

    public function testRemove()
    {
        $mapping = new PropertyMapping('file', 'fileName');
        $mapping->setMapping(array(
            'upload_destination' => '',
            'delete_on_remove'   => true
        ));

        $this->factory->expects($this->once())
            ->method('fromObject')
            ->will($this->returnValue(array($mapping)));

        $filename = $this->root.'/test.txt';
        file_put_contents($filename, 'Hello World!');

        $obj = new DummyEntity();
        $obj->setFileName('test.txt');
        $obj->setFile(new UploadedFile($filename, 'test.txt', null, null, null, true));

        $this->storage->remove($obj);

        $this->assertFalse(is_file($filename));
    }
} 