<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\EventCommand;

class RemoveEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return 'REMOVE';
    }

    protected function executeEvent(?string $payload): ?string
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Do you want to delete your docker-compose file(s) ? [y/N]\n > ",
            false
        );
        $doDelete = $helper->ask($this->input, $this->output, $question);

        if ($doDelete) {
            $dockerComposeService = new DockerComposeService($this->log);
            $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the docker-compose file(s) you want to delete (e.g. 0,1) : ',
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
        return null;
    }
}
