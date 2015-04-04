<?php
namespace Module;

use JSomerstone\DaysWithout\Application,
    JSomerstone\DaysWithout\Service\StorageServiceProvider;


class ApplicationTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Application
     */
    public function testApplicationInit()
    {
        $application = new Application(
            '/vagrant/config/config.yml',
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
        $this->assertInstanceOf('JSomerstone\DaysWithout\Service\ValidationServiceProvider', $app->getValidator());
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
        $this->assertEquals('dayswithout', $config);
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
}
