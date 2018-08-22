<?php


namespace Newsfeed\Controller;

use Zend\View\Model\ViewModel;

class User
    extends AbstractAuthController
{
    public function indexAction()
    {
        return new ViewModel([

        ]);
    }
}
