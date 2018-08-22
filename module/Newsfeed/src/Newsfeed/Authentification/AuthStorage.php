<?php

namespace Newsfeed\Authentification;

class AuthStorage
    extends \Zend\Authentication\Storage\Session
{
    /**
     * @param int $rememberMe
     * @param int $time
     */
    public function setRememberMe($rememberMe = 0, $time = 1209600)
    {
        if ($rememberMe == 1) {
            $this->session->getManager()->rememberMe($time);
        }
    }

    /**
     *
     */
    public function forgetMe()
    {
        $this->session->getManager()->forgetMe();
    }
}