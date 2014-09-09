<?php
namespace JSomerstone\DaysWithoutBundle\Tests\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel;
use JSomerstone\DaysWithoutBundle\Model\UserModel;
use JSomerstone\DaysWithoutBundle\Storage\CounterStorage;
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
    }

    public function tearDown()
    {
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
        $name = 'Finding a Counter';
        $counter = new CounterModel($name);
        $this->counterStorage->store($counter);

        $result = $this->counterStorage->load($name, null);

        $this->assertEquals($counter, $result);
    }

    /**
     * @test
     */
    public function loadingCounterWithOwnerSucceeds()
    {
        $owner = new UserModel('JSomerstone');
        $originalCounter = new CounterModel('Successfull tests', null, $owner);
        $this->counterStorage->store($originalCounter);

        $result = $this->counterStorage->load('Successfull tests', 'JSomerstone');
        $this->assertEquals($originalCounter, $result);
    }
} 
