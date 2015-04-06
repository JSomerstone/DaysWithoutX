<?php
namespace Module\Service;

use JSomerstone\DaysWithout\Service\StorageServiceProvider;
use JSomerstone\DaysWithout\Application;
use JSomerstone\DaysWithout\Storage\UserStorage;
use JSomerstone\DaysWithout\Storage\CounterStorage;

/**
 * Class StorageServiceTest
 * @package Module\Service
 * @covers JSomerstone\DaysWithout\Service\StorageServiceProvider
 */
class StorageServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $mockClient = $this->getMoncoClientMock();
        $fakeDb = 'irrelevant';

        $storageService = new StorageServiceProvider($mockClient, $fakeDb);

        $this->assertInstanceOf('JSomerstone\DaysWithout\Service\StorageServiceProvider', $storageService);

        return $storageService;
    }

    /**
     * @param StorageServiceProvider $provider
     * @test
     * @depends testInit
     */
    public function testRegistration(StorageServiceProvider $provider)
    {
        $app = $this->getApplicationMock();
        $provider->register($app);
    }

    /**
     * @param StorageServiceProvider $provider
     * @test
     * @depends testInit
     */
    public function testGetStorageFunctions(StorageServiceProvider $provider)
    {
        $this->assertInstanceOf(
            'JSomerstone\DaysWithout\Storage\UserStorage',
            $provider->getUserStorage()
        );
        $this->assertInstanceOf(
            'JSomerstone\DaysWithout\Storage\CounterStorage',
            $provider->getCounterStorage()
        );
    }

    /**
     * @return \MongoClient mock
     */
    protected function getMoncoClientMock()
    {
        return $this->getMockBuilder('\MongoClient')->getMock();
    }

    /**
     * @return Application
     */
    protected function getApplicationMock()
    {
        return $this->getMockBuilder('JSomerstone\DaysWithout\Application')
            ->disableOriginalConstructor()
            ->getMock();
    }

} 
