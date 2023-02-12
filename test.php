<?php

$vlxxID =  0;
if (array_search('-id', $argv)) {
    if (isset($argv[array_search('-id', $argv) + 1])) {
        $vlxxID = $argv[array_search('-id', $argv) + 1];
    }
}
