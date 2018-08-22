<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Hydrator;

use PHPUnit\Framework\TestCase;
use Zend\Hydrator\Exception\BadMethodCallException;
use Zend\Hydrator\ArraySerializable;
use ZendTest\Hydrator\TestAsset\ArraySerializable as ArraySerializableAsset;

/**
 * Unit tests for {@see ArraySerializable}
 *
 * @covers \Zend\Hydrator\ArraySerializable
 */
class ArraySerializableTest extends TestCase
{
    use HydratorTestTrait;

    /**
     * @var ArraySerializable
     */
    protected $hydrator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->hydrator = new ArraySerializable();
    }

    /**
     * Verify that we get an exception when trying to extract on a non-object
     */
    public function testHydratorExtractThrowsExceptionOnNonObjectParameter()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Zend\Hydrator\ArraySerializable::extract expects the provided object to implement getArrayCopy()'
        );
        $this->hydrator->extract('thisIsNotAnObject');
    }

    /**
     * Verify that we get an exception when trying to hydrate a non-object
     */
    public function testHydratorHydrateThrowsExceptionOnNonObjectParameter()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Zend\Hydrator\ArraySerializable::hydrate expects the provided object to implement'
            . ' exchangeArray() or populate()'
        );
        $this->hydrator->hydrate(['some' => 'data'], 'thisIsNotAnObject');
    }

    /**
     * Verifies that we can extract from an ArraySerializableInterface
     */
    public function testCanExtractFromArraySerializableObject()
    {
        $this->assertSame(
            [
                'foo'   => 'bar',
                'bar'   => 'foo',
                'blubb' => 'baz',
                'quo'   => 'blubb',
            ],
            $this->hydrator->extract(new ArraySerializableAsset())
        );
    }

    /**
     * Verifies we can hydrate an ArraySerializableInterface
     */
    public function testCanHydrateToArraySerializableObject()
    {
        $data = [
            'foo'   => 'bar1',
            'bar'   => 'foo1',
            'blubb' => 'baz1',
            'quo'   => 'blubb1',
        ];
        $object = $this->hydrator->hydrate($data, new ArraySerializableAsset());

        $this->assertSame($data, $object->getArrayCopy());
    }

    /**
     * Verifies that when an object already has properties,
     * these properties are preserved when it's hydrated with new data
     * existing properties should get overwritten
     *
     * @group 65
     */
    public function testWillPreserveOriginalPropsAtHydration()
    {
        $original = new ArraySerializableAsset();

        $data = [
            'bar' => 'foo1'
        ];

        $expected = array_merge($original->getArrayCopy(), $data);

        $actual = $this->hydrator->hydrate($data, $original);

        $this->assertSame($expected, $actual->getArrayCopy());
    }

    /**
     * To preserve backwards compatibility, if getArrayCopy() is not implemented
     * by the to-be hydrated object, simply exchange the array
     *
     * @group 65
     */
    public function testWillReplaceArrayIfNoGetArrayCopy()
    {
        $original = new \ZendTest\Hydrator\TestAsset\ArraySerializableNoGetArrayCopy();

        $data = [
                'bar' => 'foo1'
        ];

        $expected = $data;

        $actual = $this->hydrator->hydrate($data, $original);
        $this->assertSame($expected, $actual->getData());
    }

    public function arrayDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            //               [ existing data,  submitted data,                   expected ]
            'empty'       => [['what-exists'], [],                               []],
            'replacement' => [['what-exists'], ['zend-hydrator', 'zend-stdlib'], ['zend-hydrator', 'zend-stdlib']],
            'partial'     => [['what-exists'], ['what-exists', 'zend-hydrator'], ['what-exists', 'zend-hydrator']],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * #65 ensures that hydration will merge data into the existing object.
     * However, #66 notes that there's an issue with this when it comes to data
     * representing arrays: if the original array had data, but the submitted
     * one _removes_ data, then no change occurs. Ideally, in these cases, the
     * submitted value should _replace_ the original.
     *
     * @group 66
     * @dataProvider arrayDataProvider
     */
    public function testHydrationWillReplaceNestedArrayData($start, $submit, $expected)
    {
        $original = new ArraySerializableAsset();
        $original->exchangeArray([
            'tags' => $start,
        ]);

        $data = ['tags' => $submit];

        $actual = $this->hydrator->hydrate($data, $original);

        $final = $original->getArrayCopy();

        $this->assertSame($expected, $final['tags']);
    }
}
