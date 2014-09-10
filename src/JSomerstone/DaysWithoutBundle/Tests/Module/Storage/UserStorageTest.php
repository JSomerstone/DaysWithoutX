<?php
namespace JSomerstone\DaysWithoutBundle\Tests\Storage;

use JSomerstone\DaysWithoutBundle\Model\CounterModel;
use JSomerstone\DaysWithoutBundle\Model\UserModel;
use JSomerstone\DaysWithoutBundle\Storage\UserStorage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserStorageTest  extends WebTestCase
{

    /**
     * @var UserStorage
     */
    private $userStorage;

    /**
     * @var MongoClient
     */
    private $mongoClient;
    private $server = 'mongodb://localhost:27017';
    private $database = 'dayswithout-phpunit';

    public function setUp()
    {
        $this->mongoClient = new \MongoClient($this->server);
        $this->userStorage = new UserStorage($this->mongoClient, $this->database);
    }

    public function tearDown()
    {
        $this->mongoClient->dropDB($this->database);
    }

    /**
     * @test
     */
    public function storingPersistsUser()
    {
        $name = uniqid('testuser');
        $user = new UserModel($name);
        $this->userStorage->store($user);
    }

    /**
     * @test
     * @depends storingPersistsUser
     */
    public function existingUserIsFound()
    {
        $name = uniqid('Fooba');
        $user = new UserModel($name);
        $this->userStorage->store($user);

        $this->assertTrue($this->userStorage->exists($name));
    }

    /**
     * @test
     */
    public function nonExistingUserIsNotFound()
    {
        $this->assertFalse($this->userStorage->exists('NonExisting'));
    }

    /**
     * @test
     * @depends storingPersistsUser
     */
    public function loadReturnsPersistedUser()
    {
        $user = new UserModel('JSomerstone', uniqid());

        $this->userStorage->store($user);

        $persisted = $this->userStorage->load($user->getNick());

        $this->assertEquals($user, $persisted);
    }
} 