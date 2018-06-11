<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Hercule;
use TheAentMachine\AentDockerCompose\Aenthill\Hermes;

class AddEventCommand extends \TheAentMachine\AentDockerCompose\Aenthill\EventCommand
{
    protected function getEventName(): string
    {
        return EventEnum::ADD;
    }

    protected function executeEvent(?string $payload): void
    {
        Hercule::setHandledEvents(EventEnum::getHandledEvents());

        Hermes::dispatch(EventEnum::ASKING_FOR_DOCKER_SERVICE_INFO);
    }
}
