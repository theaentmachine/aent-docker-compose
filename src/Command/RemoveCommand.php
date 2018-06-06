<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName(Cst::REMOVE_EVENT)
            ->setDescription("An event from aenthill")
            ->setHelp('TODO')
            ->addArgument('payload', InputArgument::OPTIONAL, "The payload of the event");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO
    }
}
