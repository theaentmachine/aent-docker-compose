<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Hercule;
use TheAentMachine\AentDockerCompose\Aenthill\Hermes;

class AddEventCommand extends \TheAentMachine\AentDockerCompose\Aenthill\EventCommand
{
    protected function getEventName(): string
    {
        return EventEnum::ADD;
    }

    protected function executeEvent(string $payload): int
    {
        $exitCode = Hercule::setHandledEvents(EventEnum::getHandledEvents());
        if ($exitCode !== 0) {
            return $exitCode;
        }

        return Hermes::dispatch(EventEnum::ASKING_FOR_DOCKER_SERVICE_INFO);
    }
}
