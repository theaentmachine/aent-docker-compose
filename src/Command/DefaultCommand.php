<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('TODO')
            ->setHelp('TODO')
            ->addArgument('event', InputArgument::OPTIONAL, 'The name of the event')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The payload of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $input->getArgument('event');
        if (!$event) {
            exit(1);
        }
        try {
            $command = $this->getApplication()->find($event);
            if ($command) {
                $args = array(
                    'payload' => $input->getArgument('payload'),
                );
                $arrayInput = new ArrayInput($args);
                $command->run($arrayInput, $output);
            }
        } catch (\Exception $e) {
            $output->writeln("Unrecognized event in aent-docker-compose : '" . $event . "'");
            exit(1);
        }
    }
}
