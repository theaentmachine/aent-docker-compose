<?php

namespace TheAentMachine\AentDockerCompose\Command;

use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\Aenthill\Aenthill;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Metadata;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Command\JsonEventCommand;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Service\Service;

class NewServiceEventCommand extends JsonEventCommand
{

    protected function getEventName(): string
    {
        return 'NEW_SERVICE';
    }

    /**
     * @param array $payload
     * @return array|null
     * @throws ManifestException
     * @throws MissingEnvironmentVariableException
     * @throws \TheAentMachine\Service\Exception\ServiceException
     */
    protected function executeJsonEvent(array $payload): ?array
    {
        $service = Service::parsePayload($payload);
        if (!$service->isForMyEnvType()) {
            return null;
        }

        $fileName = Manifest::getMetadata(Metadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->getAentHelper()->title($fileName);

        $serviceName = $service->getServiceName();
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        $this->log->debug(\GuzzleHttp\json_encode($formattedPayload, JSON_PRETTY_PRINT));

        // docker-compose
        $dockerComposePath = Pheromone::getContainerProjectDirectory() . '/' . $fileName;
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        // Virtual Host
        if ($service->getNeedVirtualHost()) {
            $reverseProxyKey = Manifest::getDependencyOrNull(Metadata::REVERSE_PROXY_KEY);
            if ($reverseProxyKey === null) {
                $this->log->info('Adding aent-treafik (a reverse proxy service which can handles virtual hosts)');
                Manifest::addDependency('theaentmachine/aent-traefik:snapshot', Metadata::REVERSE_PROXY_KEY, [
                    Metadata::ENV_NAME_KEY => Manifest::getMetadata(Metadata::ENV_NAME_KEY),
                    Metadata::ENV_TYPE_KEY => Manifest::getMetadata(Metadata::ENV_TYPE_KEY),
                ]);
                $this->addAentTraefik($dockerComposePath);
            }
            $this->newVirtualHost($dockerComposePath, $serviceName);
        }
        $this->output->writeln("Service <info>$serviceName</info> has been successfully added in <info>$fileName</info>!");
        return null;
    }

    /**
     * @throws ManifestException
     * @throws \TheAentMachine\Service\Exception\ServiceException
     */
    private function addAentTraefik(string $dockerComposePath): void
    {
        $reverseProxyKey = Manifest::getDependency(Metadata::REVERSE_PROXY_KEY);
        $repliedPayloads = Aenthill::runJson($reverseProxyKey, 'ADD', []);
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);
        $service = Service::parsePayload($payload);
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        $serviceName = $service->getServiceName();
        $fileName = Manifest::getMetadata(Metadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->output->writeln("Reverse proxy <info>$serviceName</info> has been successfully added in <info>$fileName</info>!");
    }

    /**
     * @throws ManifestException
     * @throws \TheAentMachine\Service\Exception\ServiceException
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
        $reverseProxyKey = Manifest::getDependency(Metadata::REVERSE_PROXY_KEY);
        $repliedPayloads = Aenthill::runJson($reverseProxyKey, 'NEW_VIRTUAL_HOST', $message);
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);
        $service = Service::parsePayload($payload);
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        $serviceName = $service->getServiceName();
        $fileName = Manifest::getMetadata(Metadata::DOCKER_COMPOSE_FILENAME_KEY);
        $this->output->writeln("A new virtual host has been successfully added for <info>$serviceName</info> in <info>$fileName</info>!");
    }
}
