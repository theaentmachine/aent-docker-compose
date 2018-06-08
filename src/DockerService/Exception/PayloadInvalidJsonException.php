<?php

namespace TheAentMachine\AentDockerCompose\DockerService\Exception;

class PayloadInvalidJsonException extends ServiceException
{
    /**
     * PayloadInvalidJsonException constructor.
     */
    public function __construct()
    {
        parent::__construct('payload is not in valid JSON format');
    }
}
