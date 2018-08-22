<?php


namespace Newsfeed\Form\InputFilter;

use Zend\InputFilter\InputFilter;

class AbstractInputFilter
    extends InputFilter
{
    protected $objEntityManager = null;

    public function __construct(
        \Doctrine\ORM\EntityManager $objEntityManager
    )
    {
        $this->objEntityManager = $objEntityManager;
    }
}