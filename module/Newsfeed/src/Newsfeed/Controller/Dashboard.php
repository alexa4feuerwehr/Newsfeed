<?php


namespace Newsfeed\Controller;

use Zend\View\Model\ViewModel;

class Dashboard
    extends AbstractAuthController
{
    public function indexAction()
    {
        return new ViewModel([
            'objUser'   =>  $this->objUser,
        ]);
    }


    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function linkWithAmazonAction()
    {
        //
        if($this->getRequest()->getQuery('save'))
        {
            $this->objUser->AmazonUsername = $this->getRequest()->getPost()->get('AmazonUsername');
            //
            if(str_repeat('*', strlen($this->AmazonPassword)) !== $this->getRequest()->getPost()->get('AmazonPassword'))
            {
                $this->objUser->AmazonPassword = $this->getRequest()->getPost()->get('AmazonPassword');
            }
            $this->objEntityManager->merge($this->objUser);
            $this->objEntityManager->flush();
        }
        //
        return new ViewModel([
            'objUser'   =>  $this->objUser,
        ]);
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkAmazonLinkingAction()
    {
        $objAlexaService = new \Newsfeed\Service\AlexaConnect($this->objEntityManager, $this->objUser);

        // durring debugging ...
        $boolFetchLive = true;

        //
        if($objAlexaService->login($boolFetchLive))
        {
            return new ViewModel([
                'arrSelectedAlexas' =>  json_decode($this->objUser->AlexasSelected, true),
                'arrAlexas'         =>  $objAlexaService->fetchAlexas($boolFetchLive),
            ]);
        }
        else
        {
            die('<p>Konnte mich nicht bei Alexa anmelden. Benutzername oder Kennwort falsch?</p>');
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function enableDisableAlexaAction()
    {
        //
        $this->objUser->AlexasSelected = json_decode($this->objUser->AlexasSelected, true);
        //
        if(in_array($this->getRequest()->getQuery('strAlexaId'), $this->objUser->AlexasSelected))
        {
            $key = array_search($this->getRequest()->getQuery('strAlexaId'), $this->objUser->AlexasSelected);
            unset($this->objUser->AlexasSelected[$key]);
        }
        else
        {
            $this->objUser->AlexasSelected[] = $this->getRequest()->getQuery('strAlexaId');
        }
        //
        $this->objUser->AlexasSelected = json_encode($this->objUser->AlexasSelected);
        $this->objEntityManager->merge($this->objUser);
        $this->objEntityManager->flush();
        //
        die('[]');
    }
}
