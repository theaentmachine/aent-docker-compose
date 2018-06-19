<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;
use TheAentMachine\JsonEventCommand;
use TheAentMachine\Service\Service;

class NewDockerServiceInfoEventCommand extends JsonEventCommand
{

    protected function getEventName(): string
    {
        return EventEnum::NEW_DOCKER_SERVICE_INFO;
    }

    protected function executeJsonEvent(array $payload): void
    {
        $service = Service::parsePayload($payload);
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);

        // $this->log->debug(json_encode($formattedPayload, JSON_PRETTY_PRINT));

        $yml = Yaml::dump($formattedPayload, 256, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents(YamlTools::TMP_YAML_FILE, $yml);
        DockerComposeService::checkDockerComposeFileValidity(YamlTools::TMP_YAML_FILE);

        $dockerComposeService = new DockerComposeService($this->log);
        $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();
        if (count($dockerComposeFilePathnames) === 1) {
            $toMerge = $dockerComposeFilePathnames;
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the docker-compose file(s) in which the service will be added (e.g. 0,1) : ',
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
