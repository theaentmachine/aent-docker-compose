<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\JsonEventCommand;
use TheAentMachine\Service\Service;

class NewServiceEventCommand extends JsonEventCommand
{

    protected function getEventName(): string
    {
        return 'NEW_SERVICE';
    }

    protected function executeJsonEvent(array $payload): ?array
    {
        $service = Service::parsePayload($payload);
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);

        $this->log->debug(json_encode($formattedPayload, JSON_PRETTY_PRINT));

        $dockerComposeService = new DockerComposeService($this->log);
        $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();
        if (count($dockerComposeFilePathnames) === 1) {
            $filesToMerge = $dockerComposeFilePathnames;
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the docker-compose file(s) in which the service will be added (e.g. 0,1,2) : ',
                $dockerComposeFilePathnames
            );
            $question->setMultiselect(true);

            $filesToMerge = $helper->ask($this->input, $this->output, $question);
        }

        DockerComposeService::mergeContentInDockerComposeFiles($formattedPayload, $filesToMerge, true);
        return null;
    }
}
