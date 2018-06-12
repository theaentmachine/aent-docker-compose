<?php

namespace TheAentMachine\AentDockerCompose\Service\Exception;

class KeysMissingInArrayException extends ServiceException
{
    /** @var array */
    private $array;
    /** @var array */
    private $missingKeys;

    /**
     * KeysMissingInArrayException constructor.
     * @param array $array
     * @param array $missingKeys
     */
    public function __construct(array $array, array $missingKeys)
    {
        $this->array = $array;
        $this->missingKeys = $missingKeys;
        parent::__construct(json_encode($this->array) . " should have these keys : " . json_encode($this->missingKeys));
    }
}
