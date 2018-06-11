<?php


namespace TheAentMachine\AentDockerCompose\Aenthill;

/**
 * Events that have JSON payloads should extend this class.
 */
abstract class JsonEventCommand extends EventCommand
{
    /**
     * @param mixed[] $payload
     */
    abstract protected function executeJsonEvent(array $payload): void;

    protected function executeEvent(?string $payload): void
    {
        if ($payload === null) {
            throw new \InvalidArgumentException('Empty payload. JSON message expected.');
        }
        $data = \json_decode($payload, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_decode error: ' . json_last_error_msg()
            );
        }
        $this->executeJsonEvent($data);
    }
}
