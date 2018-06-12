<?php

namespace TheAentMachine\AentDockerCompose\Service\Exception;

class EmptyAttributeException extends ServiceException
{
    /** @var string */
    private $emptyAttribute;

    public function __construct(string $emptyAttribute)
    {
        $this->emptyAttribute = $emptyAttribute;
        parent::__construct("empty attribute inside service : " . $emptyAttribute);
    }
}
