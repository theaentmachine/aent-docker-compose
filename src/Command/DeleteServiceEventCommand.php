<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Yaml\Yaml;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Metadata;
use TheAentMachine\Command\JsonEventCommand;
use TheAentMachine\YamlTools\YamlTools;

class DeleteServiceEventCommand extends JsonEventCommand
{
    protected function getEventName(): string
    {
        return 'DELETE_SERVICE';
    }

    /**
     * @throws \TheAentMachine\Exception\ManifestException
     */
    protected function executeJsonEvent(array $payload): ?array
    {
        $this->log->debug(json_encode($payload, JSON_PRETTY_PRINT));
        $dockerComposeFilename = Manifest::getMetadata(Metadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->getAentHelper()->title($dockerComposeFilename);

        $ymlData = Yaml::parseFile($dockerComposeFilename);
        if (array_key_exists('serviceName', $ymlData) && array_key_exists($payload['serviceName'], $ymlData['services'])) {
            $serviceName = $payload['serviceName'];
            $elemToDelete = ['services', $serviceName];

            $doDelete = $this->getAentHelper()
                ->question("Do you want to delete the service $serviceName in $dockerComposeFilename?")
                ->yesNoQuestion()
                ->setDefault('n')
                ->ask();

            if ($doDelete) {
                $this->log->debug('deleting ' . implode('->', $elemToDelete) . ' in ' . $dockerComposeFilename);
                YamlTools::deleteYamlItem($elemToDelete, $dockerComposeFilename);
                $this->output->writeln("<info>$serviceName</info> has been successfully deleted in <info>$dockerComposeFilename</info>");
            }
        }
        if (array_key_exists('namedVolumes', $payload) && array_key_exists('volumes', $ymlData)) {
            foreach ($payload['namedVolumes'] as $namedVolume) {
                if (array_key_exists($namedVolume, $ymlData['volumes'])) {
                    $elemToDelete = ['volumes', $namedVolume];

                    $doDelete = $this->getAentHelper()
                        ->question("Do you want to delete the named volume $namedVolume in $dockerComposeFilename?")
                        ->yesNoQuestion()
                        ->setDefault('n')
                        ->ask();

                    if ($doDelete) {
                        $this->log->debug('deleting ' . implode('->', $elemToDelete) . ' in ' . $dockerComposeFilename);
                        YamlTools::deleteYamlItem($elemToDelete, $dockerComposeFilename);
                        $this->output->writeln("<info>$namedVolume</info> has been successfully deleted in <info>$dockerComposeFilename</info>");
                    }
                }
            }
        }

        return null;
    }
}
