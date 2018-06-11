<?php

namespace TheAentMachine\AentDockerCompose\Aenthill\Enum;

class EventEnum
{
    const ADD = 'ADD';
    const REMOVE = 'REMOVE';
    const ASKING_FOR_DOCKER_SERVICE_INFO = 'ASKING_FOR_DOCKER_SERVICE_INFO';
    const NEW_DOCKER_SERVICE_INFO = 'NEW_DOCKER_SERVICE_INFO';
    const DELETE_DOCKER_SERVICE = 'DELETE_DOCKER_SERVICE';

    /**
     * @return string[]
     */
    public static function getHandledEvents(): array
    {
        return array(
            EventEnum::ADD,
            EventEnum::REMOVE,
            EventEnum::NEW_DOCKER_SERVICE_INFO,
            EventEnum::DELETE_DOCKER_SERVICE
        );
    }
}
