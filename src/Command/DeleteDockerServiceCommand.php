<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;

class DeleteDockerServiceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName(Cst::DELETE_DOCKER_SERVICE_EVENT)
            ->setDescription('Delete a docker service from the docker-compose.yml')
            ->setHelp('TODO')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The payload of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $payload = json_decode($input->getArgument('payload'), true);

        // $dockerComposePath = getenv('PHEROMONE_CONTAINER_PROJECT_DIR') . '/docker-compose.yml';
        $dockerComposePath = getenv('PHEROMONE_CONTAINER_PROJECT_DIR') . '/aenthill/docker-compose.yml';

        if (empty($payload)) {
            $yml = Yaml::parseFile($dockerComposePath);
            //$service = array_search('service', array_column($yml, 'service'));
            $services = array_keys($yml['services']);
            // $volumes = $yml['volumes'];
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the services you want to detele, if any :',
                $services
            );
            $question->setMultiselect(true);
            $servicesToDelete = $helper->ask($input, $output, $question);
            print_r($servicesToDelete);
            foreach ($servicesToDelete as $s) {
                $this->deleteElementInDockerCompose('services.' . $s, $dockerComposePath, $output);
            }
        } else {
            $elemToDelete = "services." . $payload[Cst::SERVICE_NAME_KEY];
            $this->deleteElementInDockerCompose($elemToDelete, $dockerComposePath, $output);

            foreach ($payload[Cst::NAMED_VOLUMES_KEY] as $v) {
                $elemToDelete = "volumes." . $v;
                $this->deleteElementInDockerCompose($elemToDelete, $dockerComposePath, $output);
            }
        }
    }

    /**
     * @param string $element
     * @param string $file
     * @param OutputInterface $output
     * @return void
     */
    protected function deleteElementInDockerCompose(string $element, string $file, OutputInterface $output)
    {
        $cmd = array("yaml-tools", "delete", $element, "-i", $file, "-o", $file);

        $process = Utils::runAndGetProcess($cmd, $output);
        if (!$process->isSuccessful()) {
            exit($process->getExitCode());
        }
    }
}
