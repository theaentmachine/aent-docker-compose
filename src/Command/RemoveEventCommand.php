<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Metadata;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Command\EventCommand;

class RemoveEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return 'REMOVE';
    }

    /**
     * @throws \TheAentMachine\Exception\MissingEnvironmentVariableException
     * @throws \TheAentMachine\Exception\ManifestException
     */
    protected function executeEvent(?string $payload): ?string
    {
        $dockerComposeFilename = Manifest::getMetadata(Metadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->getAentHelper()->title($dockerComposeFilename);

        $doDelete = $this->getAentHelper()
            ->question("Do you want to delete $dockerComposeFilename?")
            ->yesNoQuestion()
            ->setDefault('n')
            ->ask();

        if ($doDelete) {
            $this->log->debug("Deleting $dockerComposeFilename");
            unlink(Pheromone::getContainerProjectDirectory() . "/$dockerComposeFilename");
        }
        return null;
    }
}
