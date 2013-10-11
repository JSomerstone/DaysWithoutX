<?php
include_once 'AssertionException.php';

abstract class AssertContext
{
    public static function true($condition, $messageIfNot)
    {
        if ($condition !== true)
        {
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$condition' is TRUE"
            );
        }
    }

    public static function false($condition, $message)
    {
        if ($condition !== true)
        {
            throw new AssertionException(
                $messageIfNot ?: "Failed asserting that '$condition' is TRUE"
            );
        }
    }
}