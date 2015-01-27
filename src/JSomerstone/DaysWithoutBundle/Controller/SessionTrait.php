<?php
namespace JSomerstone\DaysWithoutBundle\Controller;

use JSomerstone\DaysWithoutBundle\Exception\PublicException;
use JSomerstone\DaysWithoutBundle\Exception\SessionException;
use JSomerstone\DaysWithoutBundle\Storage\UserStorage,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Service\AuthenticationService;

trait SessionTrait
{

    /**
     * @var  AuthenticationService
     */
    protected $authenticationService;

    /**
     * @return UserModel
     */
    protected function getLoggedInUser()
    {
        return $this->getSession()->get('user');
    }

    /**
     * @return bool
     */
    protected function isLoggedIn()
    {
        return \is_a(
            $this->getLoggedInUser(),
            'JSomerstone\DaysWithoutBundle\Model\UserModel'
        );
    }

    /**
     * @param UserModel $user
     * @return mixed
     */
    protected function setLoggedInUser(UserModel $user = null)
    {
        return $this->getSession()->set('user', $user);
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    protected  function authenticateUser(UserModel $user)
    {
        return $this->getAuthenticationService()
            ->authenticateUser($user);
    }

    /**
     * @return \JSomerstone\DaysWithoutBundle\Service\AuthenticationService
     */
    private function getAuthenticationService()
    {
        if ( ! $this->authenticationService)
        {
            $this->authenticationService = $this->get('dayswithout.service.authentication');
        }
        return $this->authenticationService;
    }

    /**
     * @return UserModel
     * @throws \JSomerstone\DaysWithoutBundle\Exception\SessionException
     */
    protected function getRequestingUser()
    {
        if ($this->isLoggedIn())
        {
            return $this->getLoggedInUser();
        }
        $userName = $this->getRequest()->get('username');
        $password = $this->getRequest()->get('password');
        if ( ! $userName || ! $password)
        {
            throw new SessionException(
                'No username and/or password provided'
            );
        }

        if ( ! $this->getAuthenticationService()->authenticate($userName, $password))
        {
            throw new SessionException(
                'Wrong username and/or password given'
            );
        }
        return new UserModel($userName, $password);
    }
}
