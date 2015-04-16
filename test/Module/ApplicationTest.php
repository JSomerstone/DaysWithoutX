<?php
namespace Module;

use JSomerstone\DaysWithout\Application,
    JSomerstone\DaysWithout\Service\StorageServiceProvider;
use JSomerstone\DaysWithout\Model\CounterModel;
use JSomerstone\DaysWithout\Model\UserModel;

/**
 * Class ApplicationTest
 * @package Module
 * @covers JSomerstone\DaysWithout\Application
 */
class ApplicationTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Application
     */
    public function testApplicationInit()
    {
        $application = new Application(
            '/vagrant/config/config.behat.yml',
            '/vagrant/source/view',
            '/vagrant/source/JSomerstone/DaysWithout/Resources/validation.yml'
        );

        $this->assertInstanceOf('JSomerstone\DaysWithout\Application', $application);
        return $application;
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetStorage(Application $app)
    {
        $this->assertInstanceOf('JSomerstone\DaysWithout\Service\StorageServiceProvider', $app->getStorageService());
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetTwig(Application $app)
    {
        $this->assertInstanceOf('Twig_Environment', $app->getTwig());
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetValidator(Application $app)
    {
        $this->assertInstanceOf(
            'JSomerstone\DaysWithout\Lib\InputValidator',
            $app->getValidator()
        );
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetConfig(Application $app)
    {
        $config = $app->getConfig(null);
        $this->assertTrue(is_array($config), "Config was not an array");
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetConfigWithValue(Application $app)
    {
        $config = $app->getConfig('dwo:storage:database');
        $this->assertEquals('dayswithout-test', $config);
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetConfigFails(Application $app)
    {
        $this->assertEquals(
            null,
            $app->getConfig('any:non-existing:setting')
        );

        $this->setExpectedException('Exception');
        $app->getConfigOrFail('any:non-existing:setting');
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testDebug(Application $app)
    {
        $this->assertFalse($app['debug']);
        $app->debug(true);
        $this->assertTrue($app['debug']);
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetLogger(Application $app)
    {
        $this->assertInstanceOf(
            '\Monolog\Logger',
            $app->getLogger()
        );
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function testGetAuthenticationService(Application $app)
    {
        $this->assertInstanceOf(
            '\JSomerstone\DaysWithout\Service\AuthenticationServiceProvider',
            $app->getAuthenticationService()
        );
    }

    /**
     * @param Application $app
     * @test
     * @depends testApplicationInit
     */
    public function authenticationService(Application $app)
    {
        $service = $app->getAuthenticationService();
        $fakeUser = new UserModel('NonExisting', uniqid());
        $fakeCounter = new CounterModel('NonExisting');
        $fakeCounter->setOwner($fakeUser)->setPrivate();

        $this->assertFalse($service->authenticateUser($fakeUser));
        $this->assertFalse($service->authenticate('foobar','123'));
        $this->assertTrue($service->authoriseUserForCounter($fakeUser, $fakeCounter));
    }

    /**
     * @test
     * @depends testApplicationInit
     */
    public function nonExistingConfigCausesFailure()
    {
        $this->setExpectedException('Exception');
        $application = new Application(
            '/non/existing',
            '/vagrant/source/view',
            '/vagrant/source/JSomerstone/DaysWithout/Resources/validation.yml'
        );
    }

    /**
     * @test
     * @depends testApplicationInit
     */
    public function nonExistingViewPathCausesFailure()
    {
        $this->setExpectedException('Exception');
        $application = new Application(
            '/vagrant/config/config.yml',
            '/non/existing',
            '/vagrant/source/JSomerstone/DaysWithout/Resources/validation.yml'
        );
    }

    /**
     * @test
     * @depends testApplicationInit
     */
    public function nonExistingValidationRulesCausesFailure()
    {
        $this->setExpectedException('Exception');
        $application = new Application(
            '/vagrant/config/config.yml',
            '/vagrant/source/view',
            '/non/existing'
        );
    }
}
