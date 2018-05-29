#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use TheAentMachine\AentDockerCompose\Command\DefaultCommand;
use TheAentMachine\AentDockerCompose\Command\NewDockerService;
use TheAentMachine\AentDockerCompose\Command\DeleteDockerService;

$application = new Application();

try {
    $defaultCommand = new DefaultCommand();

    $application->add(new NewDockerService());
    $application->add(new DeleteDockerService());
    $application->add($defaultCommand);
    $application->setDefaultCommand($defaultCommand->getName());
    $application->run();
} catch (Exception $e) {
    echo $e;
}
