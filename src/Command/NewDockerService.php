<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class NewDockerService extends Command
{
    protected function configure()
    {
        $this
            ->setName('new-docker-service')
            ->setDescription('Add a docker service to the docker-compose.yml')
            ->setHelp('TODO')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The payload of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $payload = $input->getArgument('payload');

        if (empty($payload)) {
            $output->writeln("in event ". $this->getName() .": empty payload", OutputInterface::VERBOSITY_VERBOSE);
            exit(1);
        }

        $formattedPayload = Utils::formatPayloadToDockerCompose($payload);

        $yaml = Yaml::dump($formattedPayload, 100, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents('/tmp/tmp.yml', $yaml);
        $commandYamlTools = "yaml-tools merge"
            . " -i " . Constants::AENTHILL_DOCKER_COMPOSE_PATH . " /tmp/tmp.yml "
            . " -o " . Constants::AENTHILL_DOCKER_COMPOSE_PATH;
        $output->writeln(shell_exec($commandYamlTools), OutputInterface::VERBOSITY_VERBOSE);
    }
}
