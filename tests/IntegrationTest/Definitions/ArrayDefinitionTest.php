<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Scope;
use DI\Test\IntegrationTest\BaseContainerTest;

/**
 * Test array definitions.
 *
 * @coversNothing
 */
class ArrayDefinitionTest extends BaseContainerTest
{
    /**
     * @dataProvider provideContainer
     */
    public function test_array_with_values(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => [
                'value 1',
                'value 2',
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_array_containing_sub_array(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => [
                [
                    'value 1',
                    'value 2',
                ],
                [
                    'value 1',
                    'value 2',
                ],
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEquals('value 1', $array[0][0]);
        $this->assertEquals('value 2', $array[0][1]);
        $this->assertEquals('value 1', $array[1][0]);
        $this->assertEquals('value 2', $array[1][1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_array_with_links(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'links'     => [
                \DI\get('singleton'),
                \DI\get('prototype'),
            ],
            'singleton' => \DI\create('stdClass'),
            'prototype' => \DI\create('stdClass')
                ->scope(Scope::PROTOTYPE),
        ]);
        $container = $builder->build();

        $array = $container->get('links');

        $this->assertTrue($array[0] instanceof \stdClass);
        $this->assertTrue($array[1] instanceof \stdClass);

        $singleton = $container->get('singleton');
        $prototype = $container->get('prototype');

        $this->assertSame($singleton, $array[0]);
        $this->assertNotSame($prototype, $array[1]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_array_with_nested_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\create('stdClass'),
            ],
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
    }

    /**
     * An array entry is a singleton.
     * @dataProvider provideContainer
     */
    public function test_array_with_prototype_entries(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array'     => [
                \DI\get('prototype'),
            ],
            'prototype' => \DI\create('stdClass')
                ->scope(Scope::PROTOTYPE),
        ]);
        $container = $builder->build();

        $array1 = $container->get('array');
        $array2 = $container->get('array');

        $this->assertSame($array1[0], $array2[0]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_add_entries(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => [
                'value 1',
                'value 2',
            ],
        ]);
        $builder->addDefinitions([
            'values' => \DI\add([
                'another value',
                \DI\get('foo'),
            ]),
            'foo'    => \DI\create('stdClass'),
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(4, $array);
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
        $this->assertEquals('another value', $array[2]);
        $this->assertTrue($array[3] instanceof \stdClass);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_add_entries_with_nested_definitions(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'array' => [
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\create('stdClass'),
            ],
        ]);
        $builder->addDefinitions([
            'array' => \DI\add([
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'foo'),
                \DI\create('stdClass'),
            ]),
        ]);
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
        $this->assertEquals('foo', $array[2]);
        $this->assertEquals(new \stdClass, $array[3]);
    }

    /**
     * @dataProvider provideContainer
     */
    public function test_add_to_non_existing_array_works(ContainerBuilder $builder)
    {
        $builder->addDefinitions([
            'values' => \DI\add([
                'value 1',
            ]),
        ]);
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(1, $array);
        $this->assertEquals('value 1', $array[0]);
    }
}
