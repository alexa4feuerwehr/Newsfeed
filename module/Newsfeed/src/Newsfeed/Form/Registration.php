<?php


namespace Newsfeed\Form;

use Zend\Form\Element;
use Zend\Form\Element\Captcha;
use ZfServiceReCaptcha2\Captcha\ReCaptcha2;


class Registration
    extends AbstractForm
{
    public function init()
    {
        //
        $this->add([
            'name'          => 'email',
            'type'          => Element\Email::class,
            'attributes'        =>  [
                'placeholder'   =>  ('E-Mail'),
            ],
        ]);
        //
        $this->add([
            'type'       => Captcha::class,
            'name'       => 'g-recaptcha-response',
            'options'    => [
                'label' => '',
                'captcha' => [
                    'class' => ReCaptcha2::class,
                    'options' => [
                        'theme'       => 'light', // see options below
                        'public_key'  => '6Lf8sFcUAAAAAOzKdVjuNKkQplNBWD_vCrmk6OM_',
                        'private_key' => '6Lf8sFcUAAAAACcXjhL4LlJL-7Ljfd-EtJ9Zb-4L'
                    ],
                ],
            ],
        ]);
    }

}