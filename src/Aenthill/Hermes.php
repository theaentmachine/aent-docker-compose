<?php
namespace TheAentMachine\AentDockerCompose\Aenthill;

use Symfony\Component\Process\Process;

class Hermes
{
    const BINARY = 'hermes';

    public static function dispatch(string $event, ?string $payload = null): void
    {
        $command = Hermes::BINARY . " dispatch $event";
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
        $command = Hermes::BINARY . " reply $event";
        if (!empty($payload)) {
            $command .= " $payload";
        }

        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);

        $process->mustRun();
    }
}
