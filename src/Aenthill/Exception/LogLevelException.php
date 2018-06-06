<?php
namespace TheAentMachine\AentDockerCompose\Aenthill\Exception;

class LogLevelException extends AenthillException
{
    /** @var string */
    private $wrongLogLevel;

    /**
     * LogLevelException constructor.
     * @param string $wrongLogLevel
     */
    public function __construct(string $wrongLogLevel)
    {
        $this->wrongLogLevel = $wrongLogLevel;
        parent::__construct("accepted values for log level: DEBUG, INFO, WARN, ERROR. Got $wrongLogLevel");
    }
}
