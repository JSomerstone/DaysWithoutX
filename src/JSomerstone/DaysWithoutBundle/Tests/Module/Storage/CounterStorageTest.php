<?php
namespace JSomerstone\DaysWithoutBundle\Tests\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel;
use JSomerstone\DaysWithoutBundle\Model\UserModel;
use JSomerstone\DaysWithoutBundle\Storage\CounterStorage;
use JSomerstone\DaysWithoutBundle\Storage\UserStorage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CounterStorageTest  extends WebTestCase
{

    /**
     * @var CounterStorage
     */
    private $counterStorage;

    /**
     * @var MongoClient
     */
    private $mongoClient;
    private $server = 'mongodb://localhost:27017';
    private $database = 'dayswithout-phpunit';

    public function setUp()
    {
        $this->mongoClient = new \MongoClient($this->server);
        $this->counterStorage = new CounterStorage($this->mongoClient, $this->database);
        $this->mongoClient->dropDB($this->database);
    }

    /**
     * @test
     */
    public function storingCounterPersistsToDatabase()
    {
        $owner = new UserModel(uniqid('testUser'));
        $counter = new CounterModel(uniqid('testCounter'), null, $owner);

        $this->counterStorage->store($counter);
    }

    /**
     * @test
     */
    public function storingWithoutOwnerSucceeds()
    {
        $counter = new CounterModel(uniqid('testCounter'));

        $this->counterStorage->store($counter);
    }

    /**
     * @test
     */
    public function loadingExistingCounterSucceeds()
    {
        $headline = 'Finding a Counter';
        $counter = new CounterModel($headline);
        $this->counterStorage->store($counter);

        $result = $this->counterStorage->load($headline);
        $this->assertEquals($counter, $result);
    }

    /**
     * @test
     */
    public function loadingCounterWithOwnerSucceeds()
    {
        $counterName = 'Successfull tests';
        $userName = 'JSomerstone';

        $originalCounter = new CounterModel($counterName, null, new UserModel($userName));
        $this->counterStorage->store($originalCounter);

        $result = $this->counterStorage->load($counterName, $userName);
        $this->assertEquals($originalCounter, $result);
    }
} 
