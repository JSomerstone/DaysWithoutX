<?php

namespace JSomerstone\DaysWithoutBundle\Tests\Service;

use JSomerstone\DaysWithoutBundle\Model\CounterModel,
    JSomerstone\DaysWithoutBundle\Model\UserModel,
    JSomerstone\DaysWithoutBundle\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group service
 * @group authentication
 */
class AuthenticationServiceTest extends WebTestCase
{
    /**
     * Mocks UserStorage's persisted data
     * @var UserModel
     */
    private $persistedUser;

    /**
     * @test
     */
    public function testSuccessAuthentication()
    {
        $userName = 'foo';
        $correctPassword = 'correctPassword';

        $this->persistUser($userName, $correctPassword);

        $service = new AuthenticationService($this->getUserStorageMock());

        $this->assertTrue(
            $service->authenticate($userName, $correctPassword),
            'Authentication failed'
        );
    }

    /**
     * @test
     */
    public function nonExistingUserFailsToAuthenticate()
    {
        $service = new AuthenticationService($this->getUserStorageMock(false));

        $this->assertFalse(
            $service->authenticate('NonExisting', 'irrelevant'),
            'Authentication succeeded when it should not!'
        );
    }

    /**
     * @test
     */
    public function wrongPasswordFailsToAuthenticate()
    {
        $userName = 'foo';
        $correctPassword = 'correctPassword';
        $wrongPassword = str_rot13($correctPassword);

        $this->persistUser($userName, $correctPassword);

        $service = new AuthenticationService($this->getUserStorageMock());

        $this->assertFalse(
            $service->authenticate($userName, $wrongPassword),
            'Authentication succeeded when it should not!'
        );
    }

    /**
     * @test
     */
    public function counterAuthSuccess()
    {
        $user = $this->persistUser('dude', 'correctPassword');
        $counter = new CounterModel('irrelevant');

        $counter->setOwner($user);

        $service = new AuthenticationService($this->getUserStorageMock($user));

        $this->assertTrue(
            $service->authoriseUserForCounter($user, $counter),
            'Authentication for counter failed'
        );
    }

    /**
     * @test
     */
    public function counterAuthFails()
    {
        $owner = $this->persistUser('dude', 'irrelevat');

        $user = new UserModel('AnotherDude');
        $user->setPassword('irrelevant');

        $counter = new CounterModel('irrelevant');
        $counter->setOwner($owner);

        $service = new AuthenticationService($this->getUserStorageMock());

        $this->assertFalse(
            $service->authoriseUserForCounter($user, $counter),
            'Authentication for counter succeeded when it was not supposed to'
        );
    }

    /**
     * @param $userName
     * @param $password
     * @return UserModel
     */
    private function persistUser($userName, $password)
    {
        $user = new UserModel($userName);
        $user->setPassword($password);
        $this->persistedUser = $user;
        return $this->persistedUser;
    }

    /**
     * @param bool $exists
     * @return \JSomerstone\DaysWithoutBundle\Storage\UserStorage Mock
     */
    private function getUserStorageMock($exists = true)
    {
        $mock = $this->getMockBuilder('JSomerstone\DaysWithoutBundle\Storage\UserStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue($exists));

        $mock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->persistedUser));

        return $mock;
    }
}
