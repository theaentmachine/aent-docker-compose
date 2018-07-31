<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Filesystem\Filesystem;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Command\AbstractEventCommand;

class AddEventCommand extends AbstractEventCommand
{
    protected function getEventName(): string
    {
        return CommonEvents::ADD_EVENT;
    }

    /**
     * @param null|string $payload
     * @return null|string
     * @throws \TheAentMachine\Exception\CommonAentsException
     * @throws \TheAentMachine\Exception\ManifestException
     * @throws \TheAentMachine\Exception\MissingEnvironmentVariableException
     */
    protected function executeEvent(?string $payload): ?string
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->title('Installing a Docker Compose orchestrator');
        $envType = $aentHelper->getCommonQuestions()->askForEnvType();
        $envName = $aentHelper->getCommonQuestions()->askForEnvName($envType);

        $projectDir = Pheromone::getContainerProjectDirectory();
        $fileNameChoices = [];
        if (!file_exists("$projectDir/docker-compose.yml")) {
            $fileNameChoices[] = 'docker-compose.yml';
        }

        $i = 0;
        $tmpFileName = "docker-compose.$envName.yml";
        while (file_exists("$projectDir/$tmpFileName")) {
            $i++;
            $tmpFileName = "docker-compose.$envName$i.yml";
        }
        $fileNameChoices[] = $tmpFileName;

        $fileName = $aentHelper->choiceQuestion('Select your docker-compose file name', $fileNameChoices)
            ->setDefault('0')
            ->ask();

        $versionChoices = ['3.7', '3.6', '3.5', '3.4', '3.3', '3.2'];
        $version = $aentHelper->choiceQuestion('Select your docker-compose version', $versionChoices)
            ->setDefault('3.3')
            ->ask();

        $fileSystem = new Filesystem();
        $dockerComposePath = "$projectDir/$fileName";
        $fileSystem->dumpFile($dockerComposePath, "version: '$version'");
        $dirInfo = new \SplFileInfo(\dirname($dockerComposePath));
        chown($dockerComposePath, $dirInfo->getOwner());
        chgrp($dockerComposePath, $dirInfo->getGroup());
        Manifest::addMetadata(CommonMetadata::DOCKER_COMPOSE_FILENAME_KEY, $fileName);

        $this->output->writeln("Docker Compose file <info>$fileName</info> has been successfully created!");
        $aentHelper->spacer();

        $CIaentID = $aentHelper->getCommonQuestions()->askForCI();
        if (null !== $CIaentID) {
            Aenthill::run($CIaentID, CommonEvents::ADD_EVENT);
            Aenthill::run($CIaentID, CommonEvents::NEW_DEPLOY_DOCKER_COMPOSE_JOB_EVENT, $fileName);
        }

        return null;
    }
}
