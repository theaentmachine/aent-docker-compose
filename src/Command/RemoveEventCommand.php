<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\JsonEventCommand;

class RemoveEventCommand extends JsonEventCommand
{
    protected function getEventName(): string
    {
        return EventEnum::REMOVE;
    }

    protected function executeJsonEvent(array $payload): void
    {
        $dockerComposeService = new DockerComposeService($this->log);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Do you want to delete your docker-compose file(s) ? [y/N]\n > ",
            false
        );
        $doDelete = $helper->ask($this->input, $this->output, $question);

        if ($doDelete) {
            $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the docker-compose files you want to delete : ',
                $dockerComposeFilePathnames,
                null
            );
            $question->setMultiselect(true);

            $toDelete = $helper->ask($this->input, $this->output, $question);

            if (!empty($toDelete)) {
                foreach ($toDelete as $file) {
                    $this->log->info('Deleting ' . $file);
                    unlink($file);
                }
            }
        }
    }
}
