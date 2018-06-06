<?php
namespace TheAentMachine\AentDockerCompose\Aenthill;

use Symfony\Component\Console\Output\OutputInterface;
use TheAentMachine\AentDockerCompose\Aenthill\Exception\LogLevelException;

class Log
{
    /** @var array */
    private $levels = [
        "DEBUG" => OutputInterface::VERBOSITY_DEBUG,
        "INFO" => OutputInterface::VERBOSITY_VERY_VERBOSE,
        "WARN" => OutputInterface::VERBOSITY_VERBOSE,
        "ERROR" => OutputInterface::VERBOSITY_NORMAL,
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
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    /**
     * @param string $logLevel
     * @throws LogLevelException
     * @return void
     */
    public function setLevel(string $logLevel): void
    {
        if (!array_key_exists($logLevel, $this->levels)) {
            throw new LogLevelException($logLevel);
        }

        $this->output->setVerbosity($this->levels[$logLevel]);
    }

    /**
     * @param string $message
     * @return void
     */
    public function debugln(string $message): void
    {
        $this->output->writeln($message, OutputInterface::VERBOSITY_DEBUG);
    }

    /**
     * @param string $message
     * @return void
     */
    public function infoln(string $message): void
    {
        $this->output->writeln("<info>$message</info>", OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    /**
     * @param string $message
     * @return void
     */
    public function warnln(string $message): void
    {
        $this->output->writeln("<comment>$message</comment>", OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param string $message
     * @return void
     */
    public function errorln(string $message): void
    {
        $this->output->writeln("<error>$message</error>", OutputInterface::VERBOSITY_NORMAL);
    }
}
