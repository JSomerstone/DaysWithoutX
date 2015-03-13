<?php
namespace JSomerstone\DaysWithout\Service;

use JSomerstone\DaysWithout\Storage\UserStorage,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Model\CounterModel;

class AuthenticationService
{
    /**
     * @var JSomerstone\DaysWithout\Storage\UserStorage
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
    public function authoriseUserForCounter(UserModel $user, CounterModel $counter)
    {
        if ($counter->isPublic() || $counter->getOwner()->getId() === $user->getId())
        {
            return true;
        }
        return false;
    }
}
