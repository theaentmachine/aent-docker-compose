#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use \TheAentMachine\Aent\OrchestratorAent;
use \TheAentMachine\AentDockerCompose\Event\AddEvent;
use \TheAentMachine\AentDockerCompose\Event\NewServiceEvent;

$application = new OrchestratorAent('Docker Compose', new AddEvent(), new NewServiceEvent());
$application->run();
