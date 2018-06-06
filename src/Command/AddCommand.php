<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class AddCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName(Cst::ADD_EVENT)
            ->setDescription("event from aenthill")
            ->setHelp('TODO')
            ->addArgument('payload', InputArgument::OPTIONAL, "The payload of the event");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        $output->writeln("asking for docker services..", OutputInterface::VERBOSITY_VERBOSE);

        $outStr = Utils::hermesUtil("dispatch", Cst::ASK_DOCKER_SERVICE_EVENT, "{}");
        $output->writeln($outStr);
        */
    }
}
