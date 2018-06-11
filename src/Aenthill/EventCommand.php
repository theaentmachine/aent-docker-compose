<?php


namespace TheAentMachine\AentDockerCompose\Aenthill;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

abstract class EventCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    protected $log;
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;

    abstract protected function getEventName(): string;
    abstract protected function executeEvent(?string $payload): void;

    protected function configure()
    {
        $this
            ->setName($this->getEventName())
            ->setDescription('Handle the "'.$this->getEventName().'" event')
            ->addArgument('payload', InputArgument::OPTIONAL, 'The event payload');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $logLevelConfigurator = new LogLevelConfigurator($output);
        $logLevelConfigurator->configureLogLevel();

        $this->log = new ConsoleLogger($output);

        $payload = $input->getArgument('payload');
        $this->input = $input;
        $this->output = $output;

        $this->executeEvent($payload);
    }
}
