<?php
function D()
{
    $stuff = func_get_args();
    echo "\n", '***************DEBUG-start************', "\n";
    foreach ($stuff as $debug) {
        var_dump($debug);
    }
    echo '***************DEBUG-end************', "\n";
}