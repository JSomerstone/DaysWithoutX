<?php
include_once 'AssertionException.php';

abstract class AssertContext
{
    public static function true($condition, $messageIfNot = null)
    {
        if ($condition !== true)
        {
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$condition' is TRUE"
            );
        }
    }

    public static function false($condition, $messageIfNot = null)
    {
        if ($condition !== false)
        {
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$condition' is TRUE"
            );
        }
    }

    public static function regexp($regexp, $testetString, $messageIfNot = null)
    {
        if ( preg_match($regexp, $testetString) !== 1)
        {
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$testetString' matched '$regexp'"
            );
        }
    }

    public static function contains($needle, $hayStack, $messageIfNot = null)
    {
        if (stripos($hayStack, $needle) === false)
        {
            echo $hayStack;
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$hayStack' has string '$needle'"
            );
        }
    }

    public static function notContains($needle, $hayStack, $messageIfNot = null)
    {
        if (stripos($hayStack, $needle) === true)
        {
            echo $hayStack;
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$hayStack' does not have string '$needle'"
            );
        }
    }
}
