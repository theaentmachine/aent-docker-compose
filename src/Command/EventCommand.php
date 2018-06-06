<?php
namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use TheAentMachine\AentDockerCompose\Aenthill\Log;

abstract class EventCommand extends Command
{
    /** @var Log */
    protected $log;

    /** @var null|string */
    protected $payload;

    /**
     * @param Log $log
     */
    public function setLog(Log $log): void
    {
        $this->log = $log;
    }

    /**
     * @param null|string $payload
     */
    public function setPayload(?string $payload): void
    {
        $this->payload = $payload;
    }
}
