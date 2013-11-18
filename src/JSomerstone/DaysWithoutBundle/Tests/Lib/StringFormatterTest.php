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
        return [
            //Input , output
            ['This is not in camelCase', 'this-is-not-in-camelcase'],
            ['<script>alert("foobar")</script>', 'script-alert-foobar-script'],
            ['Many   whites   between', 'many-whites-between'],
            ['   Trimmer    ', 'trimmer'],
        ];
    }

    public function provideSafeStrings()
    {
        return [
            //Input , output
            ['something-url-safe', 'Something Url Safe'],
        ];
    }

}