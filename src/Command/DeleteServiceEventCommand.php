<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ConfirmationQuestion;
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

        $helper = $this->getHelper('question');
        foreach ($dockerComposeFilePathnames as $file) {
            $ymlData = Yaml::parseFile($file);
            if (array_key_exists('serviceName', $ymlData) && array_key_exists($payload['serviceName'], $ymlData['services'])) {
                $toDelete = ['services', $payload['serviceName']];
                $question = $this->getDeleteConfirmationQuestion($toDelete);
                $doDelete = $helper->ask($this->input, $this->output, $question);
                if ($doDelete) {
                    $this->log->debug('deleting ' . $toDelete . ' in ' . $file);
                    YamlTools::deleteYamlItem($toDelete, $file);
                }
            }
            if (array_key_exists('namedVolumes', $payload) && array_key_exists('volumes', $ymlData)) {
                foreach ($payload['namedVolumes'] as $namedVolume) {
                    if (array_key_exists($namedVolume, $ymlData['volumes'])) {
                        $toDelete = ['volumes',  $namedVolume];
                        $question = $this->getDeleteConfirmationQuestion($toDelete);
                        $doDelete = $helper->ask($this->input, $this->output, $question);
                        if ($doDelete) {
                            $this->log->debug('deleting ' . $toDelete . ' in ' . $file);
                            YamlTools::deleteYamlItem($toDelete, $file);
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param string[] $elementToDel
     * @param bool $default
     * @return ConfirmationQuestion
     */
    private function getDeleteConfirmationQuestion(array $elementToDel, bool $default = false): ConfirmationQuestion
    {
        return new ConfirmationQuestion(
            'Do you want to delete ' . implode('->', $elementToDel) . " ? (y/n)\n > ",
            $default
        );
    }
}
