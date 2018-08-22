<?php


namespace Newsfeed\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

abstract class AbstractController
    extends AbstractActionController
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $objEntityManager;

    /**
     * AbstractController constructor.
     * @param ContainerInterface|null $objContainer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $objContainer = null)
    {
        if($objContainer)
        {
            $this->objEntityManager = $objContainer->get('doctrine.entitymanager.orm_default');
        }
    }

    /**
     * @param $arrParams
     * @return ViewModel
     */
    protected function getViewModel($arrParams)
    {
        return new ViewModel($arrParams);
    }
}
