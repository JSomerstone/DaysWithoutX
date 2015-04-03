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

    /**
     * @param $ruleSet
     * @param $validValue
     * @param $invalidValue
     * @dataProvider provideRulesetsWithValues
     */
    public function testValidationAgainstRules($ruleSet, $validValue, $invalidValue)
    {
        $fieldName = 'irrelevant';
        $this->inputValidator->setValidationRule($fieldName, $ruleSet);
        $this->assertEmpty(
            $this->inputValidator->validateField($fieldName, $validValue)
        );
        $this->setExpectedException('JSomerstone\DaysWithout\Lib\InputValidatorValueException');
        $this->inputValidator->validateField($fieldName, $invalidValue);
    }

    public function provideRulesetsWithValues()
    {
        return array(
            'white-list' => array(
                array('white-list' => array('one', 'two', 'three')),
                'one',
                'four'
            ),

            'regexp' => array(
                array('regexp' => '/^[a-z]+$/'),
                'abba',
                'fuubar123'
            ),

            'type' => array(
                array('type' => 'bool'),
                true,
                'false'
            ),

            'min' => array(
                array('min' => 0),
                12,
                -1
            ),

            'max' => array(
                array('max' => 100),
                100,
                101
            ),

            'min-length' => array(
                array('min-length' => 3, 'type' => 'string'),
                'Abba',
                'no'
            ),

            'max-length' => array(
                array('max-length' => 6, 'type' => 'string'),
                'seven',
                'six+one'
            ),

            'custom' => array(
                array('custom' => 'isEmail'),
                'fuu@bar.com',
                'fuu at bar dot com'
            ),

            'non-empty' => array(
                array('non-empty' => 1),
                'I am not empty',
                ''
            ),
        );
    }
}
