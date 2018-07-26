<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Command\AbstractEventCommand;

class RemoveEventCommand extends AbstractEventCommand
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
        $dockerComposeFilename = Manifest::mustGetMetadata(CommonMetadata::DOCKER_COMPOSE_FILENAME_KEY);
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
