<?php

namespace JSomerstone\DaysWithout\Tests\Lib;

use JSomerstone\DaysWithout\Lib\InputValidator;
use JSomerstone\DaysWithout\Lib\InputValidatorException;

/**
 * @group lib
 */
class InputValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \JSomerstone\DaysWithout\Lib\InputValidator
     */
    protected $inputValidator;

    public function setUp()
    {
        $this->inputValidator = new InputValidator();
    }

    /**
     * @test
     * @dataProvider provideRuleSets
     */
    public function testSettingRule($ruleSet)
    {
        $this->inputValidator->setValidationRule(uniqid(), $ruleSet);
    }

    public function provideRuleSets()
    {
        return array(
            array(
                array( 'type' => 'text')
            ),
            array(
                array( 'min' => 1, 'max' => 100)
            ),
            array(
                array( 'min-length' => 1, 'max-length' => 100)
            ),
            array(
                array(
                    'type' => 'int',
                    'patter' => '[0-9]+',
                    'regexp' => '/^[0-9]+$/',
                    'message' => '',
                    'custom' => 'isEmail',
                    'min' => -PHP_INT_MAX,
                    'min-length' => 1,
                    'max' => PHP_INT_MAX,
                    'max-length' => 3600,
                    'non-empty' => true
                )
            )
        );
    }

    public function testWhiteListValidation()
    {
        $ruleSet = array('white-list' => array('one', 'two', 'three'));
        $this->inputValidator->setValidationRule('number', $ruleSet);

        $this->assertEmpty(
            $this->inputValidator->validateField('number', 'one')
        );
        $this->setExpectedException('JSomerstone\DaysWithout\Lib\InputValidatorValueException');
        $this->inputValidator->validateField('number', -13);
    }
}
