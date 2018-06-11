<?php

namespace TheAentMachine\AentDockerCompose\Aenthill;

use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\PheromoneEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Exception\LogLevelException;

class LogLevelConfigurator
{
    /** @var array */
    private $levels = [
        'DEBUG' => OutputInterface::VERBOSITY_DEBUG,
        'INFO' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'WARN' => OutputInterface::VERBOSITY_VERBOSE,
        'ERROR' => OutputInterface::VERBOSITY_NORMAL,
    ];

    /** @var OutputInterface */
    private $output;

    /**
     * Log constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @throws LogLevelException
     * @return void
     */
    public function configureLogLevel(): void
    {
        $logLevel = getenv(PheromoneEnum::PHEROMONE_LOG_LEVEL);

        if ($logLevel === false) {
            throw LogLevelException::emptyLogLevel();
        }

        if (!array_key_exists($logLevel, $this->levels)) {
            throw LogLevelException::invalidLogLevel($logLevel);
        }

        $this->output->setVerbosity($this->levels[$logLevel]);
    }
}
