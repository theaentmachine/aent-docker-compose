<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;

class DeleteDockerServiceEventCommand extends EventCommand
{
    protected function configure()
    {
        $this->setName(EventEnum::DELETE_DOCKER_SERVICE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO : ask for services to delete
        /*
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
                $services,
                null
            );
            $question->setMultiselect(true);
            $servicesToDelete = $helper->ask($input, $output, $question);

            // print_r($servicesToDelete);

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
        }*/
    }
}
