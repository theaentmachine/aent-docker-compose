<?php

namespace TheAentMachine\AentDockerCompose\DockerService\Exception;

class KeysMissingInArrayException extends \Exception
{
    /** @var array */
    private $array;
    /** @var array */
    private $missingSubKeys;

    /**
     * KeysMissingInArrayException constructor.
     * @param array $array
     * @param array $missingKeys
     */
    public function __construct(array $array, array $missingKeys)
    {
        $this->array = $array;
        $this->missingSubKeys = $missingKeys;
        parent::__construct(json_encode($this->array) . " should have these keys : " . json_encode($this->missingSubKeys));
    }
}
