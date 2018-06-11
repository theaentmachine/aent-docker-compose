<?php

namespace TheAentMachine\AentDockerCompose\Aenthill;

use Symfony\Component\Process\Process;

class Hermes
{
    public const BINARY = 'hermes';

    public static function dispatch(string $event, ?string $payload = null): void
    {
        $command = self::BINARY . " dispatch $event";
        if (!empty($payload)) {
            $command .= " $payload";
        }

        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);

        $process->mustRun();
    }

    public static function reply(string $event, ?string $payload = null): void
    {
        $command = self::BINARY . " reply $event";
        if (!empty($payload)) {
            $command .= " $payload";
        }

        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);

        $process->mustRun();
    }
}
