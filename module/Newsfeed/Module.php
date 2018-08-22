<?php

namespace Newsfeed;

use Zend\Mvc\MvcEvent;
use Monolog\ErrorHandler;
use Doctrine\DBAL\Types\Type;

class Module
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $objServiceManger;

    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $objEventManager;

    /**
     * @param MvcEvent $objEvent
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onBootstrap(MvcEvent $objEvent)
    {
        // get managers
        $this->objServiceManger = $objEvent->getApplication()->getServiceManager();
        $this->objEventManager = $objEvent->getApplication()->getEventManager();

        // bind router
        $moduleRouteListener = new \Zend\Mvc\ModuleRouteListener();
        $moduleRouteListener->attach($this->objEventManager);

        // bin logger & Errorhandler
        ErrorHandler::register($this->objServiceManger->get('EnliteMonologService'));

        // bind layout listener
        $objLayoutListener = new \Newsfeed\View\LayoutListener();
        $objLayoutListener->attach($this->objEventManager);

        // bind auth check
        $this->objEventManager->getSharedManager()->attach(
            \Zend\Mvc\Controller\AbstractActionController::class,
            MvcEvent::EVENT_DISPATCH,
            [$this, 'onDispatch'],
            100
        );

        // Init Doctrine Password
        if(!Type::hasType('password'))
        {
            Type::addType('password', 'Cpliakas\Password\Doctrine\PasswordType');
        }
    }

    /**
     * @param MvcEvent $event
     * @return mixed
     */
    public function onDispatch(MvcEvent $event)
    {
        //
        $controller = $event->getTarget();

        //
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $strActionName = $event->getRouteMatch()->getParam('action', null);

        // Get AuthenticationService and do the verification.
        $objAuthService = $this->objServiceManger->get(\Newsfeed\Service\Authentication::class);

        // Execute the access filter on every controller except AuthController (to avoid infinite redirect).
        if ($controllerName!=\Newsfeed\Controller\Login::class && $controllerName!=\Newsfeed\Controller\Setup::class)
        {
            // user eingeloggt?
            if(!$objAuthService->getUser())
            {
                return $controller->redirect()->toUrl('/Login/login');
            }
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
