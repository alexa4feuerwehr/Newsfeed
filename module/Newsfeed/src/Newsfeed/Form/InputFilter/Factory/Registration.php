<?php

namespace Newsfeed\Form\InputFilter\Factory;

use Newsfeed\Form\InputFilter;
use Interop\Container\ContainerInterface;

class Registration
{
    public function __invoke(ContainerInterface $objServiceManager)
    {
        return new InputFilter\Registration($objServiceManager->get('doctrine.entitymanager.orm_default'));
    }
}
