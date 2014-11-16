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

    public function testGetLatestCountersExists()
    {
        $this->assertTrue(method_exists($this->counterStorage, 'getLatestCounters'), "Missing required method");
    }
    /**
     * @test
     * @depends testGetLatestCountersExists
     */
    public function loadLatestCountersWithoutCounters()
    {

        $this->assertCount(0, $this->counterStorage->getLatestCounters());
    }

    /**
     * @test
     * @depends testGetLatestCountersExists
     */
    public function loadLatestCountersWithCounters()
    {
        $countersInDb = 12;
        $expectedCounters = 10;

        for ($i = 0; $i < $countersInDb ; $i++)
        {
            $this->counterStorage->store(
                new CounterModel(
                    uniqid('Counter '),
                    date('Y-m-d', time() - rand(0, 60*60*24*365))
                )
            );
        }
        $this->assertCount($expectedCounters, $this->counterStorage->getLatestCounters());
    }

    /**
     * @test
     */
    public function creationDateIsPreserved()
    {
        $creationDate = new \DateTime('-7 days');
        $headline = 'Irrelevant';

        $counter = new CounterModel($headline, null, null, $creationDate);
        $this->counterStorage->store($counter);

        $persisted = $this->counterStorage->load($headline);

        $this->assertEquals($creationDate, $persisted->getCreated());
    }

    /**
     * @test
     */
    public function usersCountersAreFilteredByVisibility()
    {
        $getPrivateCounters = false;

        $owner = new UserModel('Irrelevant');
        $private = new CounterModel('Private', null, $owner);
        $private->setPrivate();
        $protected = new CounterModel('Protected', null, $owner);
        $protected->setProtected();
        $public = new CounterModel('Public', null, $owner);
        $public->setPublic();

        $this->counterStorage->store($private)->store($protected)->store($public);

        $list = $this->counterStorage->getUsersCounters($owner->getNick(), $getPrivateCounters);
        $this->assertCount(2, $list, var_export($list, true));
    }



    /**
     * @test
     */
    public function privateCountersCanBeListed()
    {
        $getPrivateCounters = true;

        $owner = new UserModel('Irrelevant');
        $private = new CounterModel('Private', null, $owner);
        $private->setPrivate();
        $protected = new CounterModel('Protected', null, $owner);
        $protected->setProtected();
        $public = new CounterModel('Public', null, $owner);
        $public->setPublic();

        $this->counterStorage->store($private)->store($protected)->store($public);

        $list = $this->counterStorage->getUsersCounters($owner->getNick(), $getPrivateCounters);
        $this->assertCount(3, $list);
    }

    /**
     * @test
     */
    public function removingCounterSucceeds()
    {
        $counter = new CounterModel('removable');
        $this->counterStorage->store($counter);
        $this->assertTrue($this->counterStorage->exists('removable'));

        $this->counterStorage->remove($counter);
        $this->assertFalse($this->counterStorage->exists('removable'));
    }
}
