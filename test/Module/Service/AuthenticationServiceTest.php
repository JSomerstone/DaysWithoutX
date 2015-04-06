<?php
namespace Module\Service;

use JSomerstone\DaysWithout\Application;
use JSomerstone\DaysWithout\Model\UserModel;
use JSomerstone\DaysWithout\Model\CounterModel;
use JSomerstone\DaysWithout\Service\AuthenticationServiceProvider;
use JSomerstone\DaysWithout\Storage\UserStorage;

/**
 * Class AuthenticationServiceTest
 * @package Module\Service
 * @covers \JSomerstone\DaysWithout\Service\AuthenticationServiceProvider
 */
class AuthenticationServiceTest extends \PHPUnit_Framework_TestCase
{
    private $username = 'JohnDoe';
    private $userPass = 'password';

    public function testInit()
    {
        $userStorage = $this->getUserStorageMock();
        $AuthenticationService = new AuthenticationServiceProvider($userStorage);

        $this->assertInstanceOf('JSomerstone\DaysWithout\Service\AuthenticationServiceProvider', $AuthenticationService);

        return $AuthenticationService;
    }

    /**
     * @param AuthenticationServiceProvider $provider
     * @test
     * @depends testInit
     */
    public function testRegistration(AuthenticationServiceProvider $provider)
    {
        $app = $this->getApplicationMock();
        $provider->register($app);
    }
    /**
     * @param AuthenticationServiceProvider $provider
     * @test
     * @depends testInit
     */
    public function testBoot(AuthenticationServiceProvider $provider)
    {
        $app = $this->getApplicationMock();
        $provider->boot($app);
    }

    /**
     * @test
     * @depends testInit
     */
    public function testAuthenticate()
    {
        $authenticator = new AuthenticationServiceProvider($this->getUserStorageMock());

        $this->assertTrue(
            $authenticator->authenticate($this->username, $this->userPass)
        );

        $this->assertFalse(
            $authenticator->authenticate($this->username, 'anything else')
        );
    }

    /**
     * @test
     * @depends testInit
     */
    public function testAuthenticateUser()
    {
        $authenticator = new AuthenticationServiceProvider($this->getUserStorageMock());
        $okUser = new UserModel($this->username, $this->userPass);
        $notOkUser = new UserModel(uniqid(), uniqid());

        $this->assertTrue(
            $authenticator->authenticateUser($okUser)
        );

        $this->assertFalse(
            $authenticator->authenticateUser($notOkUser)
        );
    }

    /**
     * @test
     * @depends testInit
     */
    public function testAuthenticateUserForCounter()
    {
        $authenticator = new AuthenticationServiceProvider($this->getUserStorageMock());
        $okUser = new UserModel($this->username, $this->userPass);
        $anotherUser = new UserModel(uniqid('name'), uniqid('password'));

        $counterA = new CounterModel('Counter A');
        $counterA->setOwner($okUser)->setPrivate();

        $counterB = new CounterModel('Counter B');
        $counterB->setOwner($anotherUser)->setPrivate();


        $this->assertTrue(
            $authenticator->authoriseUserForCounter($okUser, $counterA)
        );

        $this->assertFalse(
            $authenticator->authoriseUserForCounter($okUser, $counterB)
        );
    }

    /**
     * @return Application
     */
    protected function getApplicationMock()
    {
        $mock = $this->getMockBuilder('JSomerstone\DaysWithout\Application')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return UserStorage mock
     */
    protected function getUserStorageMock()
    {
        $fakeUser = new UserModel($this->username, $this->userPass);

        $mock = $this->getMockBuilder('JSomerstone\DaysWithout\Storage\UserStorage')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('exists')->willReturn(true);
        $mock->method('load')->willReturn($fakeUser);

        return $mock;
    }

} 
