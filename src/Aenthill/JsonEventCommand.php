<?php


namespace TheAentMachine\AentDockerCompose\Aenthill;


use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Events that have JSON payloads should extend this class.
 */
abstract class JsonEventCommand extends EventCommand
{
    abstract protected function executeJsonEvent(array $payload): int;

    protected function executeEvent(string $payload): int
    {
        $data = \json_decode($payload, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_decode error: ' . json_last_error_msg()
            );
        }
        return $this->executeJsonEvent($data);
    }
}
