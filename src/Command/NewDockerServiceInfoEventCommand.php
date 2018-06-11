<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Aenthill\JsonEventCommand;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\AentDockerCompose\Service\Service;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;

class NewDockerServiceInfoEventCommand extends JsonEventCommand
{

    protected function getEventName(): string
    {
        return EventEnum::NEW_DOCKER_SERVICE_INFO;
    }

    protected function executeJsonEvent(array $payload): void
    {
        $service = Service::parsePayload($payload);
        $formattedPayload = $service->serializeToDockerComposeService(false);
        $yml = Yaml::dump($formattedPayload, 256, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents(YamlTools::TMP_YAML_FILE, $yml);

        $dockerComposeService = new DockerComposeService($this->log);
        $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();
        if (count($dockerComposeFilePathnames) === 1) {
            $toMerge = $dockerComposeFilePathnames;
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the docker-compose files in which the service will be added : ',
                $dockerComposeFilePathnames,
                null
            );
            $question->setMultiselect(true);

            $toMerge = $helper->ask($this->input, $this->output, $question);
        }

        foreach ($toMerge as $file) {
            YamlTools::merge($file, YamlTools::TMP_YAML_FILE, $file);
        }
        unlink(YamlTools::TMP_YAML_FILE);
    }
}