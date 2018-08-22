<?php

namespace Newsfeed\Controller;

use Interop\Container\ContainerInterface;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result;
use Zend\Session\Container;

class Login
    extends AbstractController
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $objEntityManager = null;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $objServiceManager = null;

    /**
     * AbstractLoginController constructor.
     * @param ContainerInterface|null $objContainer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $objContainer = null)
    {
        parent::__construct($objContainer);
        //
        $this->objServiceManager = $objContainer;
        $this->objEntityManager = $objContainer->get('doctrine.entitymanager.orm_default');
    }

    /**
     * @return ContainerInterface|null|\Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        return $this->objServiceManager;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function loginAction()
    {
        //
        $objAuthService = $this->getServiceManager()->get(\Newsfeed\Service\Authentication::class);

        //
        if ($objAuthService->getIdentity())
        {
            return $this->sendToDasboard();
        }

        //
        $objViewModel = new ViewModel();

        //
        if ($this->getRequest()->isPost())
        {
            /** @var \Newsfeed\Service\Authentication $objAuthService */
            $objAuthService->getAdapter()->setIdentity(strtolower($this->getRequest()->getPost()->get('Username')), $this->getRequest()->getPost()->get('Password'));
            //
            $objViewModel->objResult = $objAuthService->authenticate();
            //
            if($objViewModel->objResult->getCode()==Result::SUCCESS)
            {
                return $this->sendToDasboard();
            }
        }

        //
        return $objViewModel;
    }

    /**
     *
     */
    public function logoutAction()
    {
        //
        $objAuthService = $this->getServiceManager()->get(\Newsfeed\Service\Authentication::class);
        //
        $objAuthService->clearIdentity();
        //
        return $this->redirect()->toUrl('/Login/login');
    }

    /**
     * @return \Zend\Http\Response
     */
    protected function sendToDasboard()
    {
        return $this->redirect()->toUrl('/Dashboard/index');
    }
}