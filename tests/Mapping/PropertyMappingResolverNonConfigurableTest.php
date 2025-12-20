<?php

namespace Vich\UploaderBundle\Tests\Mapping;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingResolver;
use Vich\UploaderBundle\Naming\NamerInterface;

final class PropertyMappingResolverNonConfigurableTest extends TestCase
{
    public function testNonConfigurableNamerWithKeepExtensionThrowsException(): void
    {
        $nonConfigurableNamer = new class() implements NamerInterface {
            public function name(object|array $object, PropertyMappingInterface $mapping): string
            {
                return 'non_configurable_name.txt';
            }
        };

        $resolver = new PropertyMappingResolver(
            ['non_configurable_namer' => $nonConfigurableNamer],
            [],
            [
                'test_mapping' => [
                    'upload_destination' => '/tmp',
                    'uri_prefix' => '/',
                    'namer' => ['service' => 'non_configurable_namer', 'options' => []],
                    'directory_namer' => ['service' => null, 'options' => null],
                    'delete_on_remove' => true,
                    'erase_fields' => true,
                    'delete_on_update' => true,
                    'inject_on_load' => false,
                    'namer_keep_extension' => true, // <-- This should throw an exception
                    'db_driver' => 'orm',
                ],
            ]
        );

        $object = new \stdClass();
        $mappingData = [
            'mapping' => 'test_mapping',
            'propertyName' => 'file',
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Namer non_configurable_namer does not implement ConfigurableInterface but namer_keep_extension option is set to true in mapping "test_mapping"');

        $resolver->resolve($object, 'file', $mappingData);
    }

    public function testNonConfigurableNamerWithKeepExtensionFalseWorksNormally(): void
    {
        $nonConfigurableNamer = new class() implements NamerInterface {
            public function name(object|array $object, PropertyMappingInterface $mapping): string
            {
                return 'non_configurable_name.txt';
            }
        };

        $resolver = new PropertyMappingResolver(
            ['non_configurable_namer' => $nonConfigurableNamer],
            [],
            [
                'test_mapping' => [
                    'upload_destination' => '/tmp',
                    'uri_prefix' => '/',
                    'namer' => ['service' => 'non_configurable_namer', 'options' => []],
                    'directory_namer' => ['service' => null, 'options' => null],
                    'delete_on_remove' => true,
                    'erase_fields' => true,
                    'delete_on_update' => true,
                    'inject_on_load' => false,
                    'namer_keep_extension' => false, // <-- This should work fine
                    'db_driver' => 'orm',
                ],
            ]
        );

        $object = new \stdClass();
        $mappingData = [
            'mapping' => 'test_mapping',
            'propertyName' => 'file',
        ];

        $mapping = $resolver->resolve($object, 'file', $mappingData);

        self::assertTrue($mapping->hasNamer());
    }
}
