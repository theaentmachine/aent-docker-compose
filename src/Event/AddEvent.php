<?php

namespace TheAentMachine\AentDockerCompose\Event;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use Symfony\Component\Filesystem\Filesystem;
use TheAentMachine\Aent\Context\BaseOrchestratorContext;
use TheAentMachine\Aent\Context\Context;
use TheAentMachine\Aent\Context\ContextInterface;
use TheAentMachine\Aent\Event\Orchestrator\AbstractOrchestratorAddEvent;
use TheAentMachine\Aent\Payload\CI\DockerComposeDeployJobPayload;
use TheAentMachine\Aent\Payload\ReverseProxy\ReverseProxyAddPayload;
use TheAentMachine\Aent\Registry\ColonyRegistry;
use TheAentMachine\Aent\Registry\Exception\ColonyRegistryException;
use TheAentMachine\AentDockerCompose\Context\DockerComposeContext;
use TheAentMachine\AentDockerCompose\Helper\DockerComposeHelper;
use TheAentMachine\Aenthill\Aenthill;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use function Safe\sprintf;
use function Safe\chown;
use function Safe\chgrp;
use TheAentMachine\Service\Exception\ServiceException;
use TheAentMachine\Service\Service;

final class AddEvent extends AbstractOrchestratorAddEvent
{
    /** @var DockerComposeContext */
    private $context;

    /** @var ColonyRegistry */
    private $reverseProxyServiceRegistry;

    /**
     * @return ContextInterface
     * @throws ColonyRegistryException
     * @throws FilesystemException
     * @throws MissingEnvironmentVariableException
     * @throws ServiceException
     * @throws StringsException
     */
    protected function setup(): ContextInterface
    {
        $this->reverseProxyServiceRegistry = ColonyRegistry::reverseProxyServiceRegistry();
        $this->context = new DockerComposeContext(BaseOrchestratorContext::fromMetadata());
        $this->context->setDockerComposeFilename($this->getDockerComposeFilename());
        $this->createDockerComposeFile();
        $this->output->writeln(sprintf("\n👌 Alright, I've created the file <info>%s</info>!", $this->context->getDockerComposeFilename()));
        $this->prompt->printAltBlock("Docker Compose: adding reverse proxy...");
        $this->addReverseProxy();
        return $this->context;
    }

    /**
     * @param ContextInterface $context
     * @return ContextInterface
     */
    protected function addDeployJobInCI(ContextInterface $context): ContextInterface
    {
        $payload = new DockerComposeDeployJobPayload($this->context->getDockerComposeFilename());
        Aenthill::runJson(DockerComposeContext::CI_DEPENDENCY_KEY, 'DOCKER_COMPOSE_DEPLOY_JOB', $payload->toArray());
        return $this->context;
    }

    /**
     * @return string
     */
    private function getDockerComposeFilename(): string
    {
        $environmentType = $this->context->getEnvironmentType();
        $environmentName = $this->context->getEnvironmentName();
        $projectDir = $this->context->getProjectDir();
        $items = [];
        if (!\file_exists("$projectDir/docker-compose.yml")) {
            $items[] = 'docker-compose.yml';
        }
        $i = 0;
        $tmpFileName = "docker-compose.$environmentName.yml";
        while (\file_exists("$projectDir/$tmpFileName")) {
            $i++;
            $tmpFileName = "docker-compose.$environmentName$i.yml";
        }
        $items[] = $tmpFileName;
        $text = "\nYour <info>Docker Compose</info> filename for your <info>$environmentType</info> environment <info>$environmentName</info>";
        $helpText = "By default, <info>Docker Compose</info> will look for a file named <info>docker-compose.yml</info> when you run <info>docker-compose up</info>. Otherwise, you should run <info>docker-compose -f filename up</info>.";
        return $this->prompt->select($text, $items, $helpText, $items[0], true) ?? '';
    }

    /**
     * @return void
     * @throws StringsException
     * @throws FilesystemException
     */
    private function createDockerComposeFile(): void
    {
        $fileSystem = new Filesystem();
        $dockerComposePath = $this->context->getDockerComposeFilePath();
        $fileSystem->dumpFile($dockerComposePath, sprintf("version: '%s'", DockerComposeHelper::VERSION));
        $dirInfo = new \SplFileInfo(\dirname($dockerComposePath));
        chown($dockerComposePath, $dirInfo->getOwner());
        chgrp($dockerComposePath, $dirInfo->getGroup());
    }

    /**
     * @return void
     * @throws ColonyRegistryException
     * @throws FilesystemException
     * @throws ServiceException
     * @throws StringsException
     */
    private function addReverseProxy(): void
    {
        $aent = $this->reverseProxyServiceRegistry->getAent(ColonyRegistry::TRAEFIK);
        $context = Context::fromMetadata();
        Aenthill::register($aent->getImage(), DockerComposeContext::REVERSE_PROXY_SERVICE_DEPENDENCY_KEY, $context->toArray());
        $payload = new ReverseProxyAddPayload($this->context->getBaseVirtualHost());
        $response = Aenthill::runJson(DockerComposeContext::REVERSE_PROXY_SERVICE_DEPENDENCY_KEY, 'ADD_REVERSE_PROXY', $payload->toArray());
        $assoc = \GuzzleHttp\json_decode($response[0], true);
        $service = Service::parsePayload($assoc);
        $serializedService = DockerComposeHelper::dockerComposeServiceSerialize($service);
        DockerComposeHelper::mergeContentInDockerComposeFile($serializedService, $this->context->getDockerComposeFilePath(), true);
    }
}
