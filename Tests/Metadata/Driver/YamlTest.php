<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use Doctrine\ORM\Mapping\ClassMetadata;
use Vich\UploaderBundle\Metadata\Driver\Yaml;

/**
 * YamlTest
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class YamlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testInconsistentYamlFile()
    {
        $rClass = new \ReflectionClass('\DateTime');
        $driver = $this->getDriver($rClass);

        $driver->mapping_content = array();

        $driver->loadMetadataForClass($rClass);
    }

    /**
     * @dataProvider fieldsProvider
     */
    public function testLoadMetadataForClass($mapping, $expectedMetadata)
    {
        $rClass = new \ReflectionClass('\DateTime');

        $driver = $this->getDriver($rClass);
        $driver->mapping_content = array(
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
        $driver = new TestableYaml($fileLocator);

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
                    'mapping'               => 'dummy_file',
                    'filename_property'     => 'fileName',
                )
            ),
            'metadata' => array(
                'file' => array(
                    'mapping'               => 'dummy_file',
                    'propertyName'          => 'file',
                    'fileNameProperty'      => 'fileName',
                    'fileRemoveProperty'    => null,
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
                    'mapping'               => 'dummy_file',
                    'propertyName'          => 'file',
                    'fileNameProperty'      => 'fileName',
                    'fileRemoveProperty'    => null,
                ),
                'image' => array(
                    'mapping'               => 'dummy_image',
                    'propertyName'          => 'image',
                    'fileNameProperty'      => 'imageName',
                    'fileRemoveProperty'    => null,
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

class TestableYaml extends Yaml
{
    public $mapping_content;

    protected function loadMappingFile($file)
    {
        return $this->mapping_content;
    }
}
