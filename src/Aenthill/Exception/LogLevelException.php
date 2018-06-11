<?php
namespace TheAentMachine\AentDockerCompose\Aenthill\Exception;

use TheAentMachine\AentDockerCompose\Aenthill\Enum\PheromoneEnum;

class LogLevelException extends AenthillException
{
    public static function invalidLogLevel(string $wrongLogLevel): self
    {
        return new self("Accepted values for log level: DEBUG, INFO, WARN, ERROR. Got '$wrongLogLevel'");
    }

    public static function emptyLogLevel(): self
    {
        return new self('Could not find environment variable ' . PheromoneEnum::PHEROMONE_LOG_LEVEL);
    }
}
