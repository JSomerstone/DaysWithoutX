<?php

namespace JSomerstone\DaysWithoutBundle\Lib;


abstract class StringFormatter
{
    public static function getUrlSafe($unsafe)
    {
        $lower = strtolower($unsafe);
        $clean = preg_replace('/[^a-z0-9_\ \-]/', '', $lower);
        return preg_replace('/[\ ]/', '-', $clean);
    }

    public static function getUrlUnsafe($safe)
    {
        $withSpaces = str_replace('-', ' ', $safe);
        return ucfirst($withSpaces);
    }
} 