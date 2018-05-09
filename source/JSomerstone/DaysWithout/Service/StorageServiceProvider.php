<?php
namespace JSomerstone\DaysWithout\Service;
use Silex\Application;
use Silex\ServiceProviderInterface;
use JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Storage\UserStorage;

use MongoDB\Client;

class StorageServiceProvider implements ServiceProviderInterface
{
    const SERVICE = 'storage';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var CounterStorage
     */
    private $counterStorage;

    /**
     * @var UserStorage
     */
    private $userStorage;

    /**
     * @param \MongoDB\Client $mongoClient
     * @param string $databaseName
     */
    public function __construct(Client $mongoClient, $databaseName)
    {
        $this->userStorage = new UserStorage($mongoClient, $databaseName);
        $this->counterStorage = new CounterStorage($mongoClient, $databaseName);
    }

    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app[self::SERVICE] = $this;
        $app['storage.user'] = $this->userStorage;
        $app['storage.counter'] = $this->counterStorage;
        $this->app = $app;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {

    }

    /**
     * @return UserStorage
     */
    public function getUserStorage()
    {
        return $this->userStorage;
    }

    /**
     * @return CounterStorage
     */
    public function getCounterStorage()
    {
        return $this->counterStorage;
    }
}
