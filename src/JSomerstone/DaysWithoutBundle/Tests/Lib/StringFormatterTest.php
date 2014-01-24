<?php

namespace JSomerstone\DaysWithoutBundle\Tests\Lib;

use JSomerstone\DaysWithoutBundle\Lib\StringFormatter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group lib
 */
class StringFormatterTest extends WebTestCase
{
    /**
     * @param $input
     * @param $expectedOutput
     * @test
     * @dataProvider provideUnsafeStrings
     */
    public function urlSafeReturnsExpected($input, $expectedOutput)
    {
        $this->assertEquals(
            $expectedOutput,
            StringFormatter::getUrlSafe($input)
        );
    }

    /**
     * @param $input
     * @param $expectedOutput
     * @test
     * @dataProvider provideSafeStrings
     */
    public function getUrlUnsafeReturnsExpected($input, $expectedOutput)
    {
        $this->assertEquals(
            $expectedOutput,
            StringFormatter::getUrlUnsafe($input)
        );
    }

    public function provideUnsafeStrings()
    {
        return array(
            //Input , output
            array('This is not in camelCase', 'this-is-not-in-camelcase'),
            array('<script>alert("foobar")</script>', 'script-alert-foobar-script'),
            array('Many   whites   between', 'many-whites-between'),
            array('   Trimmer    ', 'trimmer'),
        );
    }

    public function provideSafeStrings()
    {
        return array(
            //Input , output
            array('something-url-safe', 'Something Url Safe'),
        );
    }

}