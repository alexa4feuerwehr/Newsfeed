<?php


namespace Newsfeed\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\Model\ViewModel;

class Controller
    implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        //
        $controller = new $requestedName($container);

        // Get AuthenticationService and do the verification.
        $objAuthService = $container->get(\Newsfeed\Service\Authentication::class);

        //
        if($requestedName != \Newsfeed\Controller\Login::class && $objAuthService->getUser())
        {
            $controller->setUser($objAuthService->getUser());
        }
        //
        return $controller;
    }
}