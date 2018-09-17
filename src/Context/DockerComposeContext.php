<?php

namespace TheAentMachine\AentDockerCompose\Context;

use Safe\Exceptions\StringsException;
use TheAentMachine\Aent\Context\Context;
use TheAentMachine\Aenthill\Aenthill;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use function Safe\sprintf;

final class DockerComposeContext extends Context
{
    public const CI_DEPENDENCY_KEY = 'CI';

    /** @var string */
    private $projectDir;

    /** @var string */
    private $dockerComposeFilename;

    /**
     * DockerComposeContext constructor.
     * @param Context $context
     * @throws MissingEnvironmentVariableException
     */
    public function __construct(Context $context)
    {
        parent::__construct($context->getType(), $context->getName(), $context->getBaseVirtualHost());
        $this->projectDir = Pheromone::getContainerProjectDirectory();
    }

    /**
     * @return void
     */
    public function toMetadata(): void
    {
        parent::toMetadata();
        Aenthill::update([
           'DOCKER_COMPOSE_FILENAME' => $this->dockerComposeFilename,
        ]);
    }

    /**
     * @return self
     * @throws MissingEnvironmentVariableException
     */
    public static function fromMetadata()
    {
        $self = new self(parent::fromMetadata());
        $self->dockerComposeFilename = Aenthill::metadata('DOCKER_COMPOSE_FILENAME');
        return $self;
    }

    /**
     * @return string
     * @throws StringsException
     */
    public function getDockerComposeFilePath(): string
    {
        return sprintf('%s/%s', $this->projectDir, $this->dockerComposeFilename);
    }

    /**
     * @return string
     */
    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    /**
     * @return string
     */
    public function getDockerComposeFilename(): string
    {
        return $this->dockerComposeFilename;
    }

    /**
     * @param string $dockerComposeFilename
     */
    public function setDockerComposeFilename(string $dockerComposeFilename): void
    {
        $this->dockerComposeFilename = $dockerComposeFilename;
    }
}
