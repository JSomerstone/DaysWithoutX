<?php
namespace Module\Service;

use JSomerstone\DaysWithout\Lib\InputValidator;
use JSomerstone\DaysWithout\Service\StorageServiceProvider;
use JSomerstone\DaysWithout\Application;
use JSomerstone\DaysWithout\Service\ValidationServiceProvider;

/**
 * Class ValidationServiceTest
 * @package Module\Service
 * @covers \JSomerstone\DaysWithout\Service\ValidationServiceProvider
 */
class ValidationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $inputValidator = new InputValidator();
        $validationService = new ValidationServiceProvider(
            $inputValidator,
            '/vagrant/source/JSomerstone/DaysWithout/Resources/validation.yml'
        );

        $this->assertInstanceOf('JSomerstone\DaysWithout\Service\ValidationServiceProvider', $validationService);

        return $validationService;
    }

    /**
     * @param ValidationServiceProvider $provider
     * @test
     * @depends testInit
     */
    public function testRegistration(ValidationServiceProvider $provider)
    {
        $app = $this->getApplicationMock();
        $provider->register($app);
    }
    /**
     * @param ValidationServiceProvider $provider
     * @test
     * @depends testInit
     */
    public function testBoot(ValidationServiceProvider $provider)
    {
        $app = $this->getApplicationMock();
        $provider->boot($app);
    }

    public function testUnreadableRulePath()
    {
        $validator = new InputValidator();
        $nonExistingRulePath = '/tm/' .uniqid() . '.yml';

        $this->setExpectedException('\Exception');
        $validationService = new ValidationServiceProvider(
            $validator,
            $nonExistingRulePath
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

        $mock['validator.rules'] = array(
            'postive-integer' => array(
                'type' => 'number',
                'max' => PHP_INT_MAX,
                'min' => 0,
                'non-empty' => true
            )
        );
        return $mock;
    }

} 
