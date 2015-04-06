<?php
namespace JSomerstone\DaysWithout\Service;
use Silex\Application;
use Silex\ServiceProviderInterface;
use JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Storage\UserStorage;

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
     * @param \MongoClient $mongoClient
     * @param string $databaseName
     */
    public function __construct(\MongoClient $mongoClient, $databaseName)
    {
        $this->mongoClient = $mongoClient;
        $this->database = $databaseName;

        $this->userStorage = new UserStorage($this->mongoClient, $this->database);
        $this->counterStorage = new CounterStorage($this->mongoClient, $this->database);
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
