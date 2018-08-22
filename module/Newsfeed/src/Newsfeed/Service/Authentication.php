<?php


namespace Newsfeed\Service;


class Authentication
    extends \Zend\Authentication\AuthenticationService
{
    protected $arrConfig;

    /**
     * @var \Newsfeed\Entity\User
     */
    protected $objUser;

    public function __construct($objStorage, $objAuthAdapter, $objEntityManager, $arrConfig)
    {
        $this->arrConfig = $arrConfig;
        $this->objEntityManager = $objEntityManager;
        //
        parent::__construct($objStorage, $objAuthAdapter);
        //
        if($this->getIdentity())
        {
            $this->objUser = $this->objEntityManager->getRepository(\Newsfeed\Entity\User::class)->findOneById($this->getIdentity());
        }
    }

    /**
     * @return \Newsfeed\Entity\User $objUser
     */
    public function getUser()
    {
        return $this->objUser;
    }
}