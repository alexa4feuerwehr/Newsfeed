<?php


namespace Newsfeed\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Interop\Container\ContainerInterface;

abstract class AbstractAuthController
    extends AbstractController
{
    /**
     * @var \Newsfeed\Entity\User
     */
    protected $objUser;

    /**
     * @param \Newsfeed\Entity\User $objUser
     */
    public function setUser(\Newsfeed\Entity\User $objUser)
    {
        $this->objUser = $objUser;
    }
}