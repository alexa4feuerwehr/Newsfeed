<?php


namespace Newsfeed\Form\Factory;


use Newsfeed\Form\InputFilter;
use Newsfeed\Form;
use Interop\Container\ContainerInterface;

class Registration
{
    public function __invoke(ContainerInterface $objServiceManager)
    {
        $objRegistrationForm = new Form\Registration($objServiceManager);

        $objRegistrationForm->setInputFilter($objServiceManager->get('InputFilterManager')->get(InputFilter\Registration::class));

        $objRegistrationForm->init();

        return $objRegistrationForm;
    }
}