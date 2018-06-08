<?php

namespace TheAentMachine\AentDockerCompose\DockerService\Exception;

class PayloadKeyMissingException extends ServiceException
{
    /** @var string */
    private $missingKey;

    public function __construct(string $missingKey)
    {
        $this->missingKey = $missingKey;
        parent::__construct('key missing in the payload: ' . $missingKey);
    }
}
