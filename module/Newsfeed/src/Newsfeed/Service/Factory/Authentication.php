<?php


namespace Newsfeed\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\Storage\Session as Storage;

/**
 * Class AuthenticationService
 * @package IntiresCore\Service\Factory
 *
 * @seemore: https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/User_Management__Authentication_and_Access_Filtering/Implementing_User_Authentication.html

 */
class Authentication
    implements FactoryInterface
{
    public function __invoke(ContainerInterface $objContainer, $requestedName, array $options = null)
    {
        //
        $objEntityManager = $objContainer->get('ServiceManager')->get('doctrine.entitymanager.orm_default');

        // Get data from config files.
        $arrConfig = $objContainer->get('ServiceManager')->get('configuration');

        // Configure session storage.
        $objStorage = new \Newsfeed\Authentification\AuthStorage();

        //
        $objAuthAdapter = new \Newsfeed\Authentification\AuthAdapter($objEntityManager);

        // Return AuthenticationService.
        return new \Newsfeed\Service\Authentication($objStorage, $objAuthAdapter, $objEntityManager, $arrConfig);
    }
}
