<?php

require __DIR__ . '/vendor/autoload.php';

$generator = new Autoloader\Generator("src/");
$generator->IncludePSR0Autoloader(false)
    ->relativePaths()
    ->generate("src/autoload.php");
