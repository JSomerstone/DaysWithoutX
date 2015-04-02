<?php
namespace JSomerstone\DaysWithout\Service;
use Silex\Application;
use Silex\ServiceProviderInterface;
use JSomerstone\DaysWithout\Storage\CounterStorage,
    JSomerstone\DaysWithout\Storage\UserStorage;

class StorageServiceProvider implements ServiceProviderInterface
{
    private $mongoClient;
    private $database;

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
    }

    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['storage'] = $this;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $this->userStorage = new UserStorage($this->mongoClient, $this->database);
        $this->counterStorage = new CounterStorage($this->mongoClient, $this->database);
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
