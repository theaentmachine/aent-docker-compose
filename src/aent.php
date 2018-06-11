#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use TheAentMachine\AentDockerCompose\Aenthill\AentApplication;
use TheAentMachine\AentDockerCompose\Command\RootCommand;

$application = new AentApplication();
$rootCommand = new RootCommand();
$application->add($rootCommand);
//$application->setDefaultCommand($rootCommand->getName());

try {
    exit($application->run());
} catch (\Exception $e) {
    exit(1);
}
