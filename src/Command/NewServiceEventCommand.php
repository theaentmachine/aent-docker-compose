<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\Aenthill\Aenthill;
use TheAentMachine\Aenthill\CommonDependencies;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Command\AbstractJsonEventCommand;
use TheAentMachine\Exception\CommonAentsException;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Service\Exception\ServiceException;
use TheAentMachine\Service\Service;

class NewServiceEventCommand extends AbstractJsonEventCommand
{
    protected function getEventName(): string
    {
        return CommonEvents::NEW_SERVICE_EVENT;
    }

    /**
     * @param array $payload
     * @return array|null
     * @throws ManifestException
     * @throws MissingEnvironmentVariableException
     * @throws ServiceException
     * @throws CommonAentsException
     */
    protected function executeJsonEvent(array $payload): ?array
    {
        $service = Service::parsePayload($payload);
        if (!$service->isForMyEnvType()) {
            return null;
        }

        $fileName = Manifest::mustGetMetadata(CommonMetadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->getAentHelper()->title($fileName);

        $serviceName = $service->getServiceName();
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        $this->log->debug(\GuzzleHttp\json_encode($formattedPayload, JSON_PRETTY_PRINT));

        // docker-compose
        $dockerComposePath = Pheromone::getContainerProjectDirectory() . '/' . $fileName;
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        // Virtual Host
        if ($service->getNeedVirtualHost()) {
            if (null === Manifest::getDependency(CommonDependencies::REVERSE_PROXY_KEY)) {
                $this->getAentHelper()->getCommonQuestions()->askForReverseProxy();
                $this->runAddReverseProxy($dockerComposePath);
            }
            $this->newVirtualHost($dockerComposePath, $serviceName);
        }
        $this->output->writeln("Service <info>$serviceName</info> has been successfully added in <info>$fileName</info>!");

        return null;
    }

    /**
     * @throws ManifestException
     * @throws ServiceException
     */
    private function runAddReverseProxy(string $dockerComposePath): void
    {
        $reverseProxyKey = Manifest::mustGetDependency(CommonDependencies::REVERSE_PROXY_KEY);
        $repliedPayloads = Aenthill::runJson($reverseProxyKey, CommonEvents::ADD_EVENT, []);
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);
        $service = Service::parsePayload($payload);
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        $serviceName = $service->getServiceName();
        $fileName = Manifest::mustGetMetadata(CommonMetadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->output->writeln("Reverse proxy <info>$serviceName</info> has been successfully added in <info>$fileName</info>!");
    }

    /**
     * @throws ManifestException
     * @throws ServiceException
     */
    private function newVirtualHost(string $dockerComposePath, string $serviceName, int $virtualPort = 80, string $virtualHost = null): void
    {
        $message = [
            'service' => $serviceName,
            'virtualPort' => $virtualPort
        ];
        if ($virtualHost !== null) {
            $message['virtualHost'] = $virtualHost;
        }
        $reverseProxyKey = Manifest::mustGetDependency(CommonDependencies::REVERSE_PROXY_KEY);
        $repliedPayloads = Aenthill::runJson($reverseProxyKey, CommonEvents::NEW_VIRTUAL_HOST_EVENT, $message);
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);
        $service = Service::parsePayload($payload);
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        $serviceName = $service->getServiceName();
        $fileName = Manifest::mustGetMetadata(CommonMetadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->output->writeln("A new virtual host has been successfully added for <info>$serviceName</info> in <info>$fileName</info>!");
    }
}
