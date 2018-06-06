<?php
namespace TheAentMachine\AentDockerCompose\Aenthill;

use Symfony\Component\Process\Process;

class Hercule
{
    const BINARY = 'hercule';

    /**
     * @param string[] $events
     * @return int
     */
    public static function setHandledEvents(array $events): int
    {
        $command = Hercule::BINARY . ' set:handled-events';
        foreach ($events as $event) {
            $command .= " $event";
        }

        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);

        return $process->run();
    }
}
