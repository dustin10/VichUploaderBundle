<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use org\bovigo\vfs\vfsStream;

use Vich\UploaderBundle\Metadata\Driver\FileLocator;

/**
 * FileLocatorTest
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class FileLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    /**
     * @var FileLocator
     */
    protected $locator;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        // initialize the virtual filesystem
        $this->root = vfsStream::setup('vich_uploader_bundle', null, array(
            'vich_uploader' => array(
                'Foo.yml'       => 'some content',
                'FooBaz.yml'    => 'some content',
                'Bar.yml'       => 'some content',
                'Baz.xml'       => 'some content',
            ),
        ));

        $this->locator = new FileLocator(array(
            '\DummyNamespace' => $this->root->url() . DIRECTORY_SEPARATOR . 'vich_uploader',
        ));
    }

    /**
     * @dataProvider fileProvider
     */
    public function testFindFileForClass($namespace, $name, $extension, $path)
    {
        $rClass = $this->getMockBuilder('\ReflectionClass')
            ->disableOriginalConstructor()
            ->getMock();
        $rClass
            ->expects($this->any())
            ->method('getNamespaceName')
            ->will($this->returnValue($namespace));
        $rClass
            ->expects($this->any())
            ->method('getShortName')
            ->will($this->returnValue($name));

        $file = $this->locator->findFileForClass($rClass, $extension);

        $this->assertEquals($path, $file);
    }

    public function fileProvider()
    {
        return array(
            array( '\DummyNamespace', 'Foo', 'yml', 'vfs://vich_uploader_bundle/vich_uploader/Foo.yml' ),
            array( '\DummyNamespace', 'FooBaz', 'yml', 'vfs://vich_uploader_bundle/vich_uploader/FooBaz.yml' ),
            array( '\DummyNamespace', 'Baz', 'xml', 'vfs://vich_uploader_bundle/vich_uploader/Baz.xml' ),
            array( '\DummyNamespace', 'Dummy', 'xml', null ),
        );
    }

    /**
     * @dataProvider classesProvider
     */
    public function testFindAllClasses($extension, array $expectedClasses)
    {
        $classes = $this->locator->findAllClasses($extension);
        $classNames = array_values(array_map(function ($item) {
            return $item->getFileName();
        }, $classes));

        $this->assertEquals($expectedClasses, $classNames);
    }

    public function classesProvider()
    {
        return array(
            array( 'yml', array('Foo.yml', 'FooBaz.yml', 'Bar.yml') ),
            array( 'xml', array('Baz.xml') ),
            array( 'php', array() ),
        );
    }
}
