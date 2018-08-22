<?php


namespace Newsfeed\Controller;

use Zend\View\Model\ViewModel;

class Setup
    extends AbstractController
{
    public function indexAction()
    {
        //
        // create admin user when not exist
        //
        $objUser = $this->objEntityManager->getRepository(\Newsfeed\Entity\User::class)->findOneBy([]);
        if(!$objUser)
        {
            $objUser = new \Newsfeed\Entity\User();
            $objUser->Username = 'admin';
            $objUser->Password = 'admin';
            $this->objEntityManager->persist($objUser);
            $this->objEntityManager->flush();
        }



        return new ViewModel([




        ]);
    }
}
