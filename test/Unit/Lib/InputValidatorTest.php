<?php

namespace Lib;

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
            'comment' => array(
                array('regexp' => '/^.{0,28}$/', 'max-length' => 28),
                'Why am I not being accepted?',
                'I\'m not accepted because I am too long',
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

            'email' => array(
                array('email' => 1),
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

    public function testGetters()
    {
        $rules = array( 'nameoffield' => array('non-empty' => 1, 'min' => 4, 'max' => 10) );
        $inputValidator = new InputValidator($rules);

        $this->assertEquals(
            $rules,
            $inputValidator->getValidationRules()
        );

        $this->assertEquals(
            $rules['nameoffield'],
            $inputValidator->getValidationRule('nameoffield')
        );
    }

    public function testRequestingNonExistingRuleFails()
    {
        $inputValidator = new InputValidator();
        $this->setExpectedException('JSomerstone\DaysWithout\Lib\InputValidatorException');
        $inputValidator->getValidationRule('x');
    }

    public function testValidatingMultipleFields()
    {
        $rules = array(
            'param-one' => array('non-empty' => true),
            'param-two' => array('type' => 'string'),
        );
        $inputValidator = new InputValidator($rules);

        $post = array(
            'param-one' => 1,
            'param-two' => 'some text'
        );

        $inputValidator->validateFields($post);
    }

    public function testValidatationFails()
    {
        $rules = array(
            'required' => array('non-empty' => true),
        );
        $inputValidator = new InputValidator($rules);

        try {

            $inputValidator->validateField('required', null);
        } catch (\Exception $e)
        {
            $this->assertInstanceOf(
                'JSomerstone\DaysWithout\Lib\InputValidatorValueException',
                $e
            );
            $this->assertEquals('required: Value was empty', $e->getMessage());
            $expected = array(
                'message' => "required: Value was empty",
                'field' => "required",
                'rule' => "non-empty",
                'ruleValue' => true
            );
            $this->assertEquals($expected, $e->getData());
        }
    }

    public function testPasswordValidation()
    {
        $rule = array('password' => array('type' => 'string', 'non-empty' => true));

        $validator = new InputValidator($rule);
        $passwordOne = 'Foobar123';
        $passwordTwo = 'Foobar123';

        $validator->validatePassword($passwordOne, $passwordTwo);

        $this->setExpectedException('JSomerstone\DaysWithout\Lib\InputValidatorValueException');
        $validator->validatePassword($passwordOne, '');
    }

    public function testValidateHeadline()
    {
        $rule = array('password' => array('type' => 'string', 'non-empty' => true));

        $validator = new InputValidator($rule);
        $passwordOne = 'Foobar123';
        $passwordTwo = 'Foobar123';

        $validator->validatePassword($passwordOne, $passwordTwo);

        $this->setExpectedException('JSomerstone\DaysWithout\Lib\InputValidatorValueException');
        $validator->validatePassword($passwordOne, '');
    }
}
