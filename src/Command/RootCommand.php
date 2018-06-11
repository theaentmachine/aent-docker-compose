<?php
namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\PheromoneEnum;
use TheAentMachine\AentDockerCompose\Aenthill\LogLevelConfigurator;
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
        $logLevelConfigurator = new LogLevelConfigurator($output);
        $logLevelConfigurator->configureLogLevel();

        $log = new ConsoleLogger($output);

        $event = $input->getArgument('event');
        $payload = $input->getArgument('payload');

        $command = $this->commandFactory->createCommand($event, $payload, $log);
        //$command = $this->handleEvent($event);

        if (empty($command)) {
            $log->info("event $event is not handled by this aent, bye!");
            return 0;
        }

        $dockerCompose = new DockerCompose($logLevelConfigurator);
        try {
            $dockerCompose->seekFiles();
        } catch (\Exception $e) {
            $log->error($e->getMessage());
            return 1;
        }

        $command->setLog($logLevelConfigurator);
        $command->setPayload($payload);
        $command->setDockerCompose($dockerCompose);

        try {
            return $command->execute($input, $output);
        } catch (\Exception $e) {
            $log->errorln($e->getMessage());
            return 1;
        }
    }

    /**
     * @param string $event
     * @return null|AddEventCommand|RemoveEventCommand|NewDockerServiceInfoEventCommand|DeleteDockerServiceEventCommand
     */
    private function handleEvent(string $event): ?EventCommand
    {
        switch ($event) {
            case EventEnum::ADD:
                $eventCommand = new AddEventCommand();
                break;
            case EventEnum::REMOVE:
                $eventCommand = new RemoveEventCommand();
                break;
            case EventEnum::NEW_DOCKER_SERVICE_INFO:
                $eventCommand = new NewDockerServiceInfoEventCommand();
                break;
            case EventEnum::DELETE_DOCKER_SERVICE:
                $eventCommand = new DeleteDockerServiceEventCommand();
                break;
            default:
                return null;
        }
        $eventCommand->setHelperSet($this->getHelperSet());
        return $eventCommand;
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
