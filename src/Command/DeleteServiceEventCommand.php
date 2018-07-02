<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;
use TheAentMachine\JsonEventCommand;

class DeleteServiceEventCommand extends JsonEventCommand
{
    protected function getEventName(): string
    {
        return 'DELETE_SERVICE';
    }

    protected function executeJsonEvent(array $payload): ?array
    {
        $dockerComposeService = new DockerComposeService($this->log);
        $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();

        // $this->log->debug(json_encode($payload, JSON_PRETTY_PRINT));

        foreach ($dockerComposeFilePathnames as $file) {
            $ymlData = Yaml::parseFile($file);
            if (array_key_exists('serviceName', $ymlData) && array_key_exists($payload['serviceName'], $ymlData['services'])) {
                $serviceName = $payload['serviceName'];
                $elemToDelete = ['services', $serviceName];

                $doDelete = $this->getAentHelper()
                    ->question("Delete the service $serviceName in $file?")
                    ->yesNoQuestion()
                    ->setDefault('n')
                    ->ask();

                if ($doDelete) {
                    $this->log->debug('deleting ' . $elemToDelete . ' in ' . $file);
                    YamlTools::deleteYamlItem($elemToDelete, $file);
                }
            }
            if (array_key_exists('namedVolumes', $payload) && array_key_exists('volumes', $ymlData)) {
                foreach ($payload['namedVolumes'] as $namedVolume) {
                    if (array_key_exists($namedVolume, $ymlData['volumes'])) {
                        $elemToDelete = ['volumes', $namedVolume];

                        $doDelete = $this->getAentHelper()
                            ->question("Delete the named volume $namedVolume in $file?")
                            ->yesNoQuestion()
                            ->setDefault('n')
                            ->ask();

                        if ($doDelete) {
                            $this->log->debug('deleting ' . $elemToDelete . ' in ' . $file);
                            YamlTools::deleteYamlItem($elemToDelete, $file);
                        }
                    }
                }
            }
        }
        return null;
    }
}
