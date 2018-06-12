<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;
use TheAentMachine\JsonEventCommand;

class DeleteDockerServiceEventCommand extends JsonEventCommand
{
    protected function getEventName(): string
    {
        return EventEnum::DELETE_DOCKER_SERVICE;
    }

    protected function executeJsonEvent(array $payload): void
    {
        $dockerComposeService = new DockerComposeService($this->log);
        $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();

        print_r($payload);

        foreach ($dockerComposeFilePathnames as $file) {
            $ymlData = Yaml::parseFile($file);
            if (array_key_exists('serviceName', $ymlData) && array_key_exists($payload['serviceName'], $ymlData['services'])) {
                $toDelete = 'services.' . $payload['serviceName'];
                $this->log->info('deleting ' . $toDelete . ' in ' . $file);
                YamlTools::delete($toDelete, $file, $file);
            }
            if (array_key_exists('namedVolumes', $payload) && array_key_exists('volumes', $ymlData)) {
                foreach ($payload['namedVolumes'] as $namedVolume) {
                    if (array_key_exists($namedVolume, $ymlData['volumes'])) {
                        $toDelete = 'volumes.' . $namedVolume;
                        $this->log->info('deleting ' . $toDelete . ' in ' . $file);
                        YamlTools::delete($toDelete, $file, $file);
                    }
                }
            }
        }
    }
}
