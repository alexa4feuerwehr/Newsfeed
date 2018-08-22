<?php


namespace Newsfeed\Controller;

use Zend\View\Model\ViewModel;

class Feedmanager
    extends AbstractAuthController
{
    public function indexAction()
    {
        return new ViewModel([
            'objUser'   =>  $this->objUser,
        ]);
    }


    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function readTextAction()
    {
        $objAlexaService = new \Newsfeed\Service\AlexaConnect($this->objEntityManager, $this->objUser);
        $objAlexaService->letAlexasSaySomething(
            json_decode($this->objUser->AlexasSelected, true),
            $this->getRequest()->getPost()->get('Text')
        );
        die('[]');
    }
}
