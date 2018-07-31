#!/usr/bin/env php
<?php
/*

                    _   _____             _              _____
    /\             | | |  __ \           | |            / ____|
   /  \   ___ _ __ | |_| |  | | ___   ___| | _____ _ __| |     ___  _ __ ___  _ __   ___  ___  ___
  / /\ \ / _ \ '_ \| __| |  | |/ _ \ / __| |/ / _ \ '__| |    / _ \| '_ ` _ \| '_ \ / _ \/ __|/ _ \
 / ____ \  __/ | | | |_| |__| | (_) | (__|   <  __/ |  | |___| (_) | | | | | | |_) | (_) \__ \  __/
/_/    \_\___|_| |_|\__|_____/ \___/ \___|_|\_\___|_|   \_____\___/|_| |_| |_| .__/ \___/|___/\___|
                                                                             | |
                                                                             |_|

 */

require __DIR__ . '/../vendor/autoload.php';

use TheAentMachine\AentApplication;
use TheAentMachine\AentDockerCompose\Command\AddEventCommand;
use TheAentMachine\AentDockerCompose\Command\DeleteServiceEventCommand;
use TheAentMachine\AentDockerCompose\Command\NewServiceEventCommand;
use TheAentMachine\AentDockerCompose\Command\RemoveEventCommand;
use \TheAentMachine\Command\EnvironmentEventCommand;

$application = new AentApplication();

$application->add(new EnvironmentEventCommand());
$application->add(new AddEventCommand());
$application->add(new RemoveEventCommand());
$application->add(new NewServiceEventCommand());

$application->run();
