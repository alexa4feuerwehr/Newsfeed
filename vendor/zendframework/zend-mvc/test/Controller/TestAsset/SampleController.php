<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mvc\Controller\TestAsset;

use Zend\Mvc\Controller\AbstractActionController;

class SampleController extends AbstractActionController implements SampleInterface
{
    public function testAction()
    {
        return ['content' => 'test'];
    }

    public function testSomeStrangelySeparatedWordsAction()
    {
        return ['content' => 'Test Some Strangely Separated Words'];
    }

    public function testCircularAction()
    {
        return $this->forward()->dispatch('sample', ['action' => 'test-circular']);
    }
}
