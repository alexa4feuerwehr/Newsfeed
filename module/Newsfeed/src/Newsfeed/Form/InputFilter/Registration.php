<?php


namespace Newsfeed\Form\InputFilter;

use Zend\Filter\StringToLower;
use Zend\Filter\StringTrim;
use Zend\Validator\EmailAddress;
use Zend\Validator\Digits;


class Registration
    extends AbstractInputFilter
{
    public function init()
    {
        $this->add([
            'name'          => 'email',
            'required'      => true,
            'filters'    => [
                ['name' => StringToLower::class],
                ['name' => StringTrim::class],
            ],
            'validators'    => [
                ['name' => EmailAddress::class],
            ],
        ]);
    }
}