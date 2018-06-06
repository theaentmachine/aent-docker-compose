<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class NewDockerServiceInfoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName(Cst::NEW_DOCKER_SERVICE_INFO_EVENT)
            ->setDescription('Add a docker service to the docker-compose.yml')
            ->setHelp('TODO')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The payload of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $payload = $input->getArgument('payload');
        $formattedPayload = Utils::parsePayload($payload, $output);

        $yml = Yaml::dump($formattedPayload, 256, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents(Cst::TMP_YML_PATH, $yml);

        $dockerComposePath = getenv('PHEROMONE_CONTAINER_PROJECT_DIR') . '/docker-compose.yml';

        $yamlToolsCmd = array("yaml-tools", "merge", "-i", $dockerComposePath, Cst::TMP_YML_PATH, "-o", $dockerComposePath);
        $process = Utils::runAndGetProcess($yamlToolsCmd, $output);
        if (!$process->isSuccessful()) {
            exit($process->getExitCode());
        }

        unlink(Cst::TMP_YML_PATH);
    }
}
