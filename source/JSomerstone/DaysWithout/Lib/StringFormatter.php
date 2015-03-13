<?php

namespace JSomerstone\DaysWithout\Lib;


abstract class StringFormatter
{
    public static function getUrlSafe($unsafe)
    {
        $lower = strtolower($unsafe);
        $clean = trim(preg_replace('/[^a-z0-9_\ \-]/', ' ', $lower));
        return preg_replace('/[\ ]+/', '-', $clean);
    }

    public static function getUrlUnsafe($safe)
    {
        $parts = explode('-', $safe);
        foreach ($parts as $i => $part)
        {
            $parts[$i] = ucfirst($part);
        }
        return implode(' ', $parts);
    }
} 
