<?php
namespace JSomerstone\DaysWithoutBundle\Service;

use JSomerstone\DaysWithoutBundle\Storage\UserStorage,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Model\CounterModel;

class AuthenticationService
{
    /**
     * @var JSomerstone\DaysWithoutBundle\Storage\UserStorage
     */
    private $userStorage;

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * @param string $nick
     * @param string $password
     * @return bool
     */
    public function authenticate($nick, $password)
    {
        $user = new UserModel($nick);
        $user->setPassword($password);

        return $this->authenticateUser($user);
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    public function authenticateUser(UserModel $user)
    {
        if ( ! $this->userStorage->exists($user->getNick())) {
            return false;
        }

        $persisted = $this->userStorage->load($user->getNick());
        return ($persisted->getPassword() === $user->getPassword());
    }

    /**
     * @param UserModel $user
     * @param CounterModel $counter
     * @return bool
     */
    public function authenticateUserForCounter(UserModel $user, CounterModel $counter)
    {
        $owner = $this->userStorage->load($counter->getOwner());
        return ($user->getPassword() === $owner->getPassword());
    }
}
