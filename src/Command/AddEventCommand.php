<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\EventCommand;
use TheAentMachine\Hermes;

class AddEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return 'ADD';
    }

    protected function executeEvent(?string $payload): ?string
    {
        Hermes::dispatch('ASK_FOR_SERVICE');
        return null;
    }
}
