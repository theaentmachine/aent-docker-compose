<?php

namespace TheAentMachine\AentDockerCompose\Event;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\PcreException;
use Safe\Exceptions\StringsException;
use TheAentMachine\Aent\Event\Orchestrator\AbstractOrchestratorNewServiceEvent;
use TheAentMachine\Aent\Payload\ReverseProxy\ReverseProxyNewVirtualHostPayload;
use TheAentMachine\AentDockerCompose\Context\DockerComposeContext;
use TheAentMachine\AentDockerCompose\Helper\DockerComposeHelper;
use TheAentMachine\AentDockerCompose\Helper\EnvFileHelper;
use TheAentMachine\Aenthill\Aenthill;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Service\Environment\SharedEnvVariable;
use TheAentMachine\Service\Exception\ServiceException;
use TheAentMachine\Service\Service;

final class NewServiceEvent extends AbstractOrchestratorNewServiceEvent
{
    /** @var DockerComposeContext */
    private $context;

    /**
     * @param Service $service
     * @throws FilesystemException
     * @throws PcreException
     * @throws ServiceException
     * @throws StringsException
     * @throws MissingEnvironmentVariableException
     */
    protected function finalizeService(Service $service): void
    {
        $this->context = DockerComposeContext::fromMetadata();
        if (!empty($service->getVirtualHosts())) {
            $this->prompt->printAltBlock('Docker Compose: adding virtual host');
            $service = $this->addVirtualHost($service);
        }
        if (!empty(DockerComposeHelper::getEnvironmentVariablesForDotEnv($service))) {
            $this->prompt->printAltBlock('Docker Compose: creating dot env file...');
            $serialized = $this->createDotEnv($service);
        } else {
            $serialized= DockerComposeHelper::dockerComposeServiceSerialize($service);
        }
        DockerComposeHelper::mergeContentInDockerComposeFile($serialized, $this->context->getDockerComposeFilePath(), true);
    }

    /**
     * @param Service $service
     * @return Service
     * @throws ServiceException
     * @throws FilesystemException
     */
    private function addVirtualHost(Service $service): Service
    {
        $payload = new ReverseProxyNewVirtualHostPayload($this->context->getBaseVirtualHost(), $service);
        $response = Aenthill::runJson(DockerComposeContext::REVERSE_PROXY_SERVICE_DEPENDENCY_KEY, 'NEW_VIRTUAL_HOST', $payload->toArray());
        $assoc = \GuzzleHttp\json_decode($response[0], true);
        $service = Service::parsePayload($assoc);
        return $service;
    }

    /**
     * @param Service $service
     * @return array|mixed
     * @throws FilesystemException
     * @throws PcreException
     */
    private function createDotEnv(Service $service)
    {
        $envMapDotEnvFile = DockerComposeHelper::getEnvironmentVariablesForDotEnv($service);
        $envFilePaths = [];
        /**
         * @var string $key
         * @var SharedEnvVariable $sharedEnvVariable
         */
        foreach ($envMapDotEnvFile as $key => $sharedEnvVariable) {
            $envFilePath = '.' . $sharedEnvVariable->getContainerId() . $this->context->getEnvironmentName() . '.env';
            $envFilePaths[] = $envFilePath;
            $dotEnvFile = new EnvFileHelper($this->context->getProjectDir() . '/' . $envFilePath);
            $dotEnvFile->set($key, $sharedEnvVariable->getValue(), $sharedEnvVariable->getComment());
        }
        $envFilePaths = array_unique($envFilePaths);
        return DockerComposeHelper::dockerComposeServiceSerialize($service, $envFilePaths);
    }
}
