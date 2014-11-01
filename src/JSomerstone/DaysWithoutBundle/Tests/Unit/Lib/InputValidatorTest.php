<?php

namespace JSomerstone\DaysWithoutBundle\Tests\Lib;

use JSomerstone\DaysWithoutBundle\Lib\InputValidator;
use JSomerstone\DaysWithoutBundle\Lib\InputValidatorException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group lib
 */
class InputValidatorTest extends WebTestCase
{
    /**
     * @var \JSomerstone\DaysWithoutBundle\Lib\InputValidator
     */
    protected $inputValidator;

    public function setUp()
    {
        $this->inputValidator = new InputValidator();
    }

    /**
     * @param $field
     * @test
     * @dataProvider provideFieldNames
     */
    public function testGetRegexpForField($field)
    {
        $result = $this->inputValidator->getRegexpForField($field);
        $this->assertNotEmpty($result);
    }

    public function provideFieldNames()
    {
        return array(
            array('nick')
        );
    }

    /**
     * @dataProvider provideValidNick
     */
    public function testNickValidation($validNick)
    {
        $this->assertNull(
            $this->inputValidator->validateNick($validNick)
        );
    }

    public function provideValidNick()
    {
        return array(
            array('WTF'),
            array('Jsomerstone'),
            array('AbBa'),
        );
    }

    /**
     * @dataProvider provideInvalidNick
     */
    public function testNickValidationFailures($validNick)
    {
        $this->setExpectedException(
            'JSomerstone\DaysWithoutBundle\Lib\InputValidatorException'
        );
        $this->inputValidator->validateNick($validNick);
    }

    public function provideInvalidNick()
    {
        return array(
            array(''),
            array('      '),
            array('WT'),
            array(str_repeat('X', 49)),
            array('__POST'),
            array('L4TF'),
        );
    }

    public function provideInvalidHeadline()
    {
        return [
            [''],
            ['      '],
            ['#'],
            [str_repeat('X', 101)],
            ['!"#%!"%"#€!"#€!'],
            ['?'],
        ];
    }

    /**
     * @param $invalidHeadline
     * @test
     * @dataProvider provideInvalidHeadline
     */
    public function testHeadlineValidationFails($invalidHeadline)
    {
        $this->setExpectedException(
            'JSomerstone\DaysWithoutBundle\Lib\InputValidatorException'
        );
        $this->inputValidator->validateField('headline', $invalidHeadline);
    }
    public function provideValidHeadline()
    {
        return [
            ['It'],
            ['Sex'],
            ['WTF?!'],
            [str_repeat('X', 100)],
            ['Lol wut?'],
            ['Getting this darn thing to work'],
        ];
    }

    /**
     * @test
     * @dataProvider provideValidHeadline
     */
    public function testHeadlineValidationSucceess($validHeadline)
    {
        $this->inputValidator->validateField('headline', $validHeadline);
    }
}
