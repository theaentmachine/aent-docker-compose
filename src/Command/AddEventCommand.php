<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\Aenthill\Metadata;
use TheAentMachine\Command\EventCommand;

class AddEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return 'ADD';
    }

    protected function executeEvent(?string $payload): ?string
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->askForEnvName();
        $envType = $aentHelper->askForEnvType();

        if ($envType === Metadata::ENV_TYPE_TEST || $envType === Metadata::ENV_TYPE_PROD) {
            $aentHelper->askForCICD();
        }

        return null;
    }
}
