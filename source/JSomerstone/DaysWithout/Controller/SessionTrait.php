<?php
namespace JSomerstone\DaysWithout\Controller;

use JSomerstone\DaysWithout\Exception\PublicException;
use JSomerstone\DaysWithout\Exception\SessionException;
use JSomerstone\DaysWithout\Storage\UserStorage,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Service\AuthenticationService;

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
            'JSomerstone\DaysWithout\Model\UserModel'
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
     * @return \JSomerstone\DaysWithout\Service\AuthenticationService
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
     * @throws \JSomerstone\DaysWithout\Exception\SessionException
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
