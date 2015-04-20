<?php
namespace Module\Storage;

use JSomerstone\DaysWithout\Model\CounterModel;
use JSomerstone\DaysWithout\Model\UserModel;
use JSomerstone\DaysWithout\Storage\CounterStorage;
use JSomerstone\DaysWithout\Storage\UserStorage;

class CounterStorageTest  extends \PHPUnit_Framework_TestCase
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
    public function getResentResetCountersAreFilteredByVisibility()
    {
        $owner = new UserModel('Irrelevant');
        $private = new CounterModel('Private', date('Y-m-d', strtotime('-1 days')), $owner);
        $private->setPrivate();
        $protected = new CounterModel('Protected', date('Y-m-d', strtotime('-2 days')), $owner);
        $protected->setProtected();
        $public = new CounterModel('Public', date('Y-m-d', strtotime('-3 days')), $owner);
        $public->setPublic();

        $this->counterStorage->store($private)->store($protected)->store($public);

        $list = $this->counterStorage->getResentResetsCounters(10);
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

    /**
     * @test
     */
    public function counterResetHistoryIsPreserved()
    {
        $userModel = new UserModel('Dude');
        $counter = new CounterModel('withHistory', date('Y-m-d', strtotime('-13 days')));
        $counter->reset('This comment should be preserved', $userModel);
        $historyBefore = $counter->getHistory();
        $this->counterStorage->store($counter);

        $loadedCounter = $this->counterStorage->load($counter->getName(), $counter->getOwnerId());
        $historyAfter = $loadedCounter->getHistory();

        $this->assertEquals(
            $historyBefore,
            $historyAfter
        );
    }

    /**
     * @test
     */
    public function changeToHistoryIsPreserved()
    {
        $counter = new CounterModel('withHistory', date('Y-m-d', strtotime('-13 days')));
        $this->counterStorage->store($counter);

        $counter->reset('This comment should be preserved', null);
        $this->counterStorage->store($counter);

        $historyAfter = $this->counterStorage->load($counter->getName(), $counter->getOwnerId())->getHistory();

        $this->assertEquals(
            $counter->getHistory(),
            $historyAfter
        );
    }
}
