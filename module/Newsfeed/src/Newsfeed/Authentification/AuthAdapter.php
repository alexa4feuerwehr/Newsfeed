<?php


namespace Newsfeed\Authentification;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class AuthAdapter
    implements AdapterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $objEntityManager;

    /**
     * @var string
     */
    protected $strUsername;

    /**
     * @var string
     */
    protected $strPassword;

    /**
     * AuthAdapter constructor.
     * @param \Doctrine\ORM\EntityManager $objEntityManager
     */
    public function __construct(\Doctrine\ORM\EntityManager $objEntityManager)
    {
        $this->objEntityManager = $objEntityManager;
    }

    /**
     * @param $strUsername
     * @param $strPassword
     */
    public function setIdentity($strUsername, $strPassword)
    {
        $this->strUsername = $strUsername;
        $this->strPassword = $strPassword;
    }

    /**
     * @return Result
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authenticate()
    {
        //
        $intCode = $objUser = $strMessage = null;

        //
        /** @var \Newsfeed\Entity\User $objUserTmp */
        $objUser = $this->objEntityManager->getRepository(\Newsfeed\Entity\User::class)->findOneBy(['Username' => $this->strUsername]);

        //
        if($objUser && $objUser->Password->match($this->strPassword))
        {
            $intCode = Result::SUCCESS;
        }
        else
        {
            $intCode = Result::FAILURE_IDENTITY_NOT_FOUND;
            $strMessage = ('[No User with this username or Password was found!]');
        }

        //
        return new Result($intCode, $objUser, [$strMessage]);
    }
}