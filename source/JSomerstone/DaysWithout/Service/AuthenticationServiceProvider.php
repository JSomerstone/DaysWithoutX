<?php
namespace JSomerstone\DaysWithout\Service;

use Silex\Application;
use Silex\ServiceProviderInterface;

use JSomerstone\DaysWithout\Storage\UserStorage,
    JSomerstone\DaysWithout\Model\UserModel,
    JSomerstone\DaysWithout\Model\CounterModel;

class AuthenticationServiceProvider implements ServiceProviderInterface
{
    const SERVICE = 'authentication';

    /**
     * @var Application
     */
    public $application;

    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app[self::SERVICE] = $this;
        $this->application = $app;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }

    /**
     * @param string $nick
     * @param string $password
     * @return bool
     */
    public function authenticate($nick, $password)
    {
        return $this->authenticateUser(new UserModel($nick, $password));
    }

    /**
     * @param UserModel $user
     * @return bool
     */
    public function authenticateUser(UserModel $user)
    {
        if ( ! $this->getUserStorage()->exists($user->getNick())) {
            return false;
        }

        $persisted = $this->getUserStorage()->load($user->getNick());
        return ($persisted->getPassword() === $user->getPassword());
    }

    /**
     * @return UserStorage
     */
    private function getUserStorage()
    {
        return $this->application['storage.user'];
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
