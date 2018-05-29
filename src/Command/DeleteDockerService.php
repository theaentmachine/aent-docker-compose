<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteDockerService extends Command
{
    protected function configure()
    {
        $this
            ->setName('delete-docker-service')
            ->setDescription('Delete a docker service from the docker-compose.yml')
            ->setHelp('TODO')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The payload of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $payload = json_decode($input->getArgument('payload'), true);

        if (empty($payload)) {
            $output->writeln("empty payload");
            exit(1);
        }

        $this->deleteService($payload[Constants::SERVICE_NAME_KEY], $output);
        foreach ($payload[Constants::NAMED_VOLUMES_KEY] as $v) {
            $this->deleteNamedVolume($v, $output);
        }
    }

    /**
     * @param string $service
     * @param OutputInterface $output
     * @return null
     */
    protected function deleteService(string $service, OutputInterface $output)
    {
        $commandYamlTools = "yaml-tools delete services." . $service
            . " -i " . Constants::AENTHILL_DOCKER_COMPOSE_PATH . " -o " . Constants::AENTHILL_DOCKER_COMPOSE_PATH;
        $output->writeln(shell_exec($commandYamlTools));
    }

    /**
     * @param string $v
     * @param OutputInterface $output
     * @return null
     */
    protected function deleteNamedVolume(string $v, OutputInterface $output)
    {
        $commandYamlTools = "yaml-tools delete volumes." . $v
            . " -i " . Constants::AENTHILL_DOCKER_COMPOSE_PATH . " -o " . Constants::AENTHILL_DOCKER_COMPOSE_PATH;
        $output->writeln(shell_exec($commandYamlTools));
    }
}
