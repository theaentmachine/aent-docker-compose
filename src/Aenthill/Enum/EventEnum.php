<?php

namespace TheAentMachine\AentDockerCompose\Aenthill\Enum;

class EventEnum
{
    public const ADD = 'ADD';
    public const REMOVE = 'REMOVE';
    public const ASKING_FOR_DOCKER_SERVICE_INFO = 'ASKING_FOR_DOCKER_SERVICE_INFO';
    public const NEW_DOCKER_SERVICE_INFO = 'NEW_DOCKER_SERVICE_INFO';
    public const DELETE_DOCKER_SERVICE = 'DELETE_DOCKER_SERVICE';

    /**
     * @return string[]
     */
    public static function getHandledEvents(): array
    {
        return array(
            self::ADD,
            self::REMOVE,
            self::NEW_DOCKER_SERVICE_INFO,
            self::DELETE_DOCKER_SERVICE
        );
    }
}
