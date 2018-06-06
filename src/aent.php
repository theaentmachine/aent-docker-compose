#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use TheAentMachine\AentDockerCompose\Command\AddCommand;
use TheAentMachine\AentDockerCompose\Command\DefaultCommand;
use TheAentMachine\AentDockerCompose\Command\DeleteDockerServiceCommand;
use TheAentMachine\AentDockerCompose\Command\NewDockerServiceInfoCommand;

$application = new Application();

try {
    $defaultCommand = new DefaultCommand();
    $application->add($defaultCommand);
    $application->setDefaultCommand($defaultCommand->getName());

    $application->add(new AddCommand());
    $application->add(new NewDockerServiceInfoCommand());
    $application->add(new DeleteDockerServiceCommand());

    $application->run();
} catch (Exception $e) {
    exit(0);
}
