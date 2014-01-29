<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Doctrine\ORM\Mapping\ClassMetadata;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;

/**
 * YamlDriverTest
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class YamlDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testInconsistentYamlFile()
    {
        $rClass = new \ReflectionClass('\DateTime');
        $driver = $this->getDriver($rClass);

        $driver->mappingContent = array();

        $driver->loadMetadataForClass($rClass);
    }

    /**
     * @dataProvider fieldsProvider
     */
    public function testLoadMetadataForClass($mapping, $expectedMetadata)
    {
        $rClass = new \ReflectionClass('\DateTime');

        $driver = $this->getDriver($rClass);
        $driver->mappingContent = array(
            $rClass->name => $mapping
        );

        $metadata = $driver->loadMetadataForClass($rClass);

        $this->assertInstanceOf('\Vich\UploaderBundle\Metadata\ClassMetadata', $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals($expectedMetadata, $metadata->fields);
    }

    protected function getDriver(\ReflectionClass $class, $found = true)
    {
        $fileLocator = $this->getMock('\Metadata\Driver\FileLocatorInterface');
        $driver = new TestableYamlDriver($fileLocator);

        $fileLocator
            ->expects($this->once())
            ->method('findFileForClass')
            ->with($this->equalTo($class), $this->equalTo('yml'))
            ->will($this->returnValue($found ? 'something not null' : null));

        return $driver;
    }

    public function fieldsProvider()
    {
        $singleField = array(
            'mapping' => array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'filename_property' => 'fileName',
                )
            ),
            'metadata' => array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                )
            )
        );

        $severalFields = array(
            'mapping' => array(
                'file' => array(
                    'mapping' => 'dummy_file',
                    'filename_property' => 'fileName',
                ),
                'image' => array(
                    'mapping' => 'dummy_image',
                    'filename_property' => 'imageName',
                )
            ),
            'metadata' => array(
                'file' => array(
                    'mapping'           => 'dummy_file',
                    'propertyName'      => 'file',
                    'fileNameProperty'  => 'fileName',
                ),
                'image' => array(
                    'mapping'           => 'dummy_image',
                    'propertyName'      => 'image',
                    'fileNameProperty'  => 'imageName',
                )
            )
        );

        return array(
            array( array(), array() ),
            array( $singleField['mapping'], $singleField['metadata'] ),
            array( $severalFields['mapping'], $severalFields['metadata'] ),
        );
    }
}

class TestableYamlDriver extends YamlDriver
{
    public $mappingContent;

    protected function loadMappingFile($file)
    {
        return $this->mappingContent;
    }
}
