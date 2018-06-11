<?php

namespace TheAentMachine\AentDockerCompose\Service\Exception;

class PayloadInvalidJsonException extends ServiceException
{
    /**
     * PayloadInvalidJsonException constructor.
     */
    public function __construct()
    {
        parent::__construct('payload is not in a valid JSON format');
    }
}
