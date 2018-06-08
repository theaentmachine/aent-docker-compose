<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Hercule;
use TheAentMachine\AentDockerCompose\Aenthill\Hermes;

class AddEventCommand extends EventCommand
{
    protected function configure()
    {
        $this->setName(EventEnum::ADD);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = Hercule::setHandledEvents([EventEnum::NEW_DOCKER_SERVICE_INFO]);
        if ($exitCode !== 0) {
            return $exitCode;
        }

        return Hermes::dispatch(EventEnum::ASKING_FOR_DOCKER_SERVICE_INFO);
    }
}
