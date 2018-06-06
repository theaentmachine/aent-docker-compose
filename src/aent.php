#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use TheAentMachine\AentDockerCompose\Command\RootCommand;

$application = new Application();
$rootCommand = new RootCommand();
$application->add($rootCommand);
$application->setDefaultCommand($rootCommand->getName());

try {
    exit($application->run());
} catch (\Exception $e) {
    exit(1);
}
