<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\Command\EventCommand;

class RemoveEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return 'REMOVE';
    }

    protected function executeEvent(?string $payload): ?string
    {
        $dockerComposeService = new DockerComposeService($this->log);
        if ($dockerComposeService->filesInitialized()) {
            $doDelete = $this->getAentHelper()
                ->question('Do you want to delete your docker-compose file(s)?')
                ->yesNoQuestion()
                ->setDefault('n')
                ->ask();

            if ($doDelete) {
                $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();

                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Please choose the docker-compose file(s) you want to delete (e.g. 0,1,2) : ',
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

        return null;
    }
}
