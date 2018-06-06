<?php
namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\PheromoneEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Log;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerCompose;

class RootCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('handle')
            ->setDescription('Handle an event')
            ->setHelp('handle event [payload]')
            ->addArgument('event', InputArgument::REQUIRED, 'The event name')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The event payload');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->printWelcomeMessage($output);
        $log = new Log($output);

        try {
            $log->setLevel(getenv(PheromoneEnum::PHEROMONE_LOG_LEVEL));
        } catch (\Exception $e) {
            $log->errorln($e->getMessage());
            return 1;
        }

        $event = $input->getArgument('event');
        $payload = $input->getArgument('payload');
        $command = $this->handleEvent($event);

        if (empty($command)) {
            $log->infoln("event $event is not handled by this aent, bye!");
            return 0;
        }

        $dockerCompose = new DockerCompose($log);
        try {
            $dockerCompose->seekFiles();
        } catch (\Exception $e) {
            $log->errorln($e->getMessage());
            return 1;
        }

        $command->setLog($log);
        $command->setPayload($payload);

        return $command->execute($input, $output);
    }

    /**
     * @param string $event
     * @return null|AddEventCommand|RemoveEventCommand
     */
    private function handleEvent(string $event): ?EventCommand
    {
        switch ($event) {
            case EventEnum::ADD:
                return new AddEventCommand();
            case EventEnum::REMOVE:
                return new RemoveEventCommand();
            default:
                return null;
        }
    }

    /**
     * @param OutputInterface $output
     */
    private function printWelcomeMessage(OutputInterface $output): void
    {
        $output->writeln("
                             _   _____             _              _____
             /\             | | |  __ \           | |            / ____|
            /  \   ___ _ __ | |_| |  | | ___   ___| | _____ _ __| |     ___  _ __ ___  _ __   ___  ___  ___
           / /\ \ / _ \ '_ \| __| |  | |/ _ \ / __| |/ / _ \ '__| |    / _ \| '_ ` _ \| '_ \ / _ \/ __|/ _ \
          / ____ \  __/ | | | |_| |__| | (_) | (__|   <  __/ |  | |___| (_) | | | | | | |_) | (_) \__ \  __/
         /_/    \_\___|_| |_|\__|_____/ \___/ \___|_|\_\___|_|   \_____\___/|_| |_| |_| .__/ \___/|___/\___|
                                                                                      | |
                                                                                      |_|
        ");
    }
}
