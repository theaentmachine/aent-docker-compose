<?php
namespace TheAentMachine\AentDockerCompose\Aenthill;

use Symfony\Component\Process\Process;

class Hermes
{
    const BINARY = 'hermes';

    /**
     * @param string $event
     * @param null|string $payload
     * @return int
     */
    public static function dispatch(string $event, ?string $payload = null): int
    {
        $command = Hermes::BINARY . " dispatch $event";
        if (!empty($payload)) {
            $command .= " $payload";
        }

        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);

        return $process->run();
    }

    /**
     * @param string $event
     * @param null|string $payload
     * @return int
     */
    public static function reply(string $event, ?string $payload = null): int
    {
        $command = Hermes::BINARY . " reply $event";
        if (!empty($payload)) {
            $command .= " $payload";
        }

        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);

        return $process->run();
    }
}
