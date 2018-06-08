<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;

class RemoveEventCommand extends EventCommand
{
    protected function configure()
    {
        $this->setName(EventEnum::REMOVE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dockerComposeFilePathnames = $this->getDockerComposePathnames();
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "do you want to delete one of those docker-compose file ? [y/N]\n > ",
            false
        );
        $doDelete = $helper->ask($input, $output, $question);

        if ($doDelete) {
            $toDelete = $this->askMultiSelectQuestion($input, $output, $dockerComposeFilePathnames);
            if (!empty($toDelete)) {
                foreach ($toDelete as $file) {
                    $this->log->infoln('deleting ' . $file);
                    unlink($file);
                }
            }
        }

        return 0;
    }
}
