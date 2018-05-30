#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use TheAentMachine\AentDockerCompose\Command\DefaultCommand;
use TheAentMachine\AentDockerCompose\Command\NewDockerServiceCommand;
use TheAentMachine\AentDockerCompose\Command\DeleteDockerServiceCommand;

$application = new Application();

try {
    $defaultCommand = new DefaultCommand();

    $application->add(new NewDockerServiceCommand());
    $application->add(new DeleteDockerServiceCommand());
    $application->add($defaultCommand);
    $application->setDefaultCommand($defaultCommand->getName());
    $application->run();
} catch (Exception $e) {
    echo $e;
}
