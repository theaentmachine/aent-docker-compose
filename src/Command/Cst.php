<?php

namespace TheAentMachine\AentDockerCompose\Command;

class Cst
{
    // intercepts these events
    public const ADD_EVENT = "ADD";
    public const REMOVE_EVENT = "REMOVE";
    public const NEW_DOCKER_SERVICE_INFO_EVENT = "NEW-DOCKER-SERVICE-INFO";
    public const DELETE_DOCKER_SERVICE_EVENT = "DELETE-DOCKER-SERVICE";

    // sends these events
    // public const NEW_DOCKER_SERVICE_QUESTION_EVENT= "NEW-DOCKER-SERVICE-QUESTION";

    // payload
    public const SERVICE_NAME_KEY = "serviceName";
    public const SERVICE_KEY = "service";
    public const NAMED_VOLUMES_KEY = "namedVolumes";

    // yaml-tools
    public const TMP_YML_PATH = "./tmp.yml";
}
