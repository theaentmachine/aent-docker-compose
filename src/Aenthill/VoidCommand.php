<?php


namespace TheAentMachine\AentDockerCompose\Aenthill;

/**
 * A command that does nothing
 */
class VoidCommand extends EventCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setHidden(true);
    }

    protected function getEventName(): string
    {
        return 'void';
    }

    protected function executeEvent(?string $payload): void
    {
        // Let's do nothing.
        $this->log->debug('Event cannot be handled. Ignoring.');
    }
}
