<?php
/**
 * @link      http://github.com/zendframework/zend-json-server for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Json\Server\TestAsset;

use ArrayIterator;
use IteratorAggregate;

/**
 * @see ZF-12347
 */
class TestIteratorAggregate implements IteratorAggregate
{
    protected $array = [
        'foo' => 'bar',
        'baz' => 5,
    ];

    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }
}
