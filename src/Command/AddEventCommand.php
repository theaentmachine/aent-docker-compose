<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\EventCommand;
use TheAentMachine\Hermes;

class AddEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return EventEnum::ADD;
    }

    protected function executeEvent(?string $payload): void
    {
        Hermes::setHandledEvents(EventEnum::getHandledEvents());

        Hermes::dispatch(EventEnum::ASKING_FOR_DOCKER_SERVICE_INFO);
    }
}
