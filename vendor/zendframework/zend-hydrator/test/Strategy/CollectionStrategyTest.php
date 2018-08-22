<?php
/**
 * @see       https://github.com/zendframework/zend-hydrator for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-hydrator/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Hydrator\Strategy;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Zend\Hydrator\Exception;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\Reflection;
use Zend\Hydrator\Strategy\CollectionStrategy;
use Zend\Hydrator\Strategy\StrategyInterface;
use ZendTest\Hydrator\TestAsset;

/**
 * Tests for {@see CollectionStrategy}
 *
 * @covers \Zend\Hydrator\Strategy\CollectionStrategy
 */
class CollectionStrategyTest extends TestCase
{
    public function testImplementsStrategyInterface()
    {
        $reflection = new ReflectionClass(CollectionStrategy::class);

        $this->assertTrue($reflection->implementsInterface(StrategyInterface::class), sprintf(
            'Failed to assert that "%s" implements "%s"',
            CollectionStrategy::class,
            StrategyInterface::class
        ));
    }

    /**
     * @dataProvider providerInvalidObjectClassName
     *
     * @param mixed $objectClassName
     */
    public function testConstructorRejectsInvalidObjectClassName($objectClassName)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Object class name needs to the name of an existing class, got "%s" instead.',
            is_object($objectClassName) ? get_class($objectClassName) : gettype($objectClassName)
        ));

        new CollectionStrategy(
            $this->createHydratorMock(),
            $objectClassName
        );
    }

    /**
     * @return \Generator
     */
    public function providerInvalidObjectClassName()
    {
        $values = [
            'array'                     => [],
            'boolean-false'             => false,
            'boolean-true'              => true,
            'float'                     => mt_rand() / mt_getrandmax(),
            'integer'                   => mt_rand(),
            'null'                      => null,
            'object'                    => new stdClass(),
            'resource'                  => fopen(__FILE__, 'r'),
            'string-non-existent-class' => 'FooBarBaz9000',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    /**
     * @dataProvider providerInvalidValueForExtraction
     *
     * @param mixed $value
     */
    public function testExtractRejectsInvalidValue($value)
    {
        $strategy = new CollectionStrategy(
            $this->createHydratorMock(),
            TestAsset\User::class
        );

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Value needs to be an array, got "%s" instead.',
            is_object($value) ? get_class($value) : gettype($value)
        ));

        $strategy->extract($value);
    }

    /**
     * @return \Generator
     */
    public function providerInvalidValueForExtraction()
    {
        $values = [
            'boolean-false'             => false,
            'boolean-true'              => true,
            'float'                     => mt_rand() / mt_getrandmax(),
            'integer'                   => mt_rand(),
            'null'                      => null,
            'object'                    => new stdClass(),
            'resource'                  => fopen(__FILE__, 'r'),
            'string-non-existent-class' => 'FooBarBaz9000',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    /**
     * @dataProvider providerInvalidObjectForExtraction
     *
     * @param mixed $object
     */
    public function testExtractRejectsInvalidObject($object)
    {
        $value = [$object];

        $strategy = new CollectionStrategy(
            $this->createHydratorMock(),
            TestAsset\User::class
        );

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Value needs to be an instance of "%s", got "%s" instead.',
            TestAsset\User::class,
            is_object($object) ? get_class($object) : gettype($object)
        ));

        $strategy->extract($value);
    }

    /**
     * @return \Generator
     */
    public function providerInvalidObjectForExtraction()
    {
        $values = [
            'boolean-false'                           => false,
            'boolean-true'                            => true,
            'float'                                   => mt_rand() / mt_getrandmax(),
            'integer'                                 => mt_rand(),
            'null'                                    => null,
            'object-but-not-instance-of-object-class' => new stdClass(),
            'resource'                                => fopen(__FILE__, 'r'),
            'string-non-existent-class'               => 'FooBarBaz9000',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    public function testExtractUsesHydratorToExtractValues()
    {
        $value = [
            new TestAsset\User(),
            new TestAsset\User(),
            new TestAsset\User(),
        ];

        $extraction = function (TestAsset\User $value) {
            return spl_object_hash($value);
        };

        $hydrator = $this->createHydratorMock();

        $hydrator
            ->expects($this->exactly(count($value)))
            ->method('extract')
            ->willReturnCallback($extraction);

        $strategy = new CollectionStrategy(
            $hydrator,
            TestAsset\User::class
        );

        $expected = array_map($extraction, $value);

        $this->assertSame($expected, $strategy->extract($value));
    }

    /**
     * @dataProvider providerInvalidValueForHydration
     *
     * @param mixed $value
     */
    public function testHydrateRejectsInvalidValue($value)
    {
        $strategy = new CollectionStrategy(
            $this->createHydratorMock(),
            TestAsset\User::class
        );

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Value needs to be an array, got "%s" instead.',
            is_object($value) ? get_class($value) : gettype($value)
        ));

        $strategy->hydrate($value);
    }

    /**
     * @return \Generator
     */
    public function providerInvalidValueForHydration()
    {
        $values = [
            'boolean-false'             => false,
            'boolean-true'              => true,
            'float'                     => mt_rand() / mt_getrandmax(),
            'integer'                   => mt_rand(),
            'null'                      => null,
            'object'                    => new stdClass(),
            'resource'                  => fopen(__FILE__, 'r'),
            'string-non-existent-class' => 'FooBarBaz9000',
        ];

        foreach ($values as $key => $value) {
            yield $key => [$value];
        }
    }

    public function testHydrateUsesHydratorToHydrateValues()
    {
        $value = [
            ['name' => 'Suzie Q.'],
            ['name' => 'John Doe'],
        ];

        $hydration = function ($data) {
            static $hydrator;

            if (null === $hydrator) {
                $hydrator = new Reflection();
            }

            return $hydrator->hydrate(
                $data,
                new TestAsset\User()
            );
        };

        $hydrator = $this->createHydratorMock();

        $hydrator
            ->expects($this->exactly(count($value)))
            ->method('hydrate')
            ->willReturnCallback($hydration);

        $strategy = new CollectionStrategy(
            $hydrator,
            TestAsset\User::class
        );

        $expected = array_map($hydration, $value);

        $this->assertEquals($expected, $strategy->hydrate($value));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HydratorInterface
     */
    private function createHydratorMock()
    {
        return $this->createMock(HydratorInterface::class);
    }
}
