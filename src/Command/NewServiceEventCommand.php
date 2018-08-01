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
        $dockerComposePath = Pheromone::getContainerProjectDirectory() . '/' . $fileName;

        $this->getAentHelper()->title($fileName);

        // Virtual Host
        if ($service->getNeedVirtualHost()) {
            if (null === Manifest::getDependency(CommonDependencies::REVERSE_PROXY_KEY)) {
                $this->getAentHelper()->getCommonQuestions()->askForReverseProxy();
                $this->runAddReverseProxy($dockerComposePath);
            }
            $service = $this->newVirtualHost($service);
        }

        if ($service->getNeedBuild()) {
            $service = $this->newImageToBuild($service);
        }

        $serviceName = $service->getServiceName();
        $formattedPayload = DockerComposeService::dockerComposeServiceSerialize($service);
        $this->log->debug(\GuzzleHttp\json_encode($formattedPayload, JSON_PRETTY_PRINT));

        // docker-compose
        $dockerComposePath = Pheromone::getContainerProjectDirectory() . '/' . $fileName;
        DockerComposeService::mergeContentInDockerComposeFile($formattedPayload, $dockerComposePath, true);

        $this->output->writeln("Service <info>$serviceName</info> has been successfully added in <info>$fileName</info>!");

        return null;
    }

    /**
     * @param string $dockerComposePath
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
     * @param Service $service
     * @return Service
     * @throws ManifestException
     * @throws ServiceException
     */
    private function newVirtualHost(Service $service): Service
    {
        $reverseProxyKey = Manifest::mustGetDependency(CommonDependencies::REVERSE_PROXY_KEY);
        $repliedPayloads = Aenthill::runJson($reverseProxyKey, CommonEvents::NEW_VIRTUAL_HOST_EVENT, $service->jsonSerialize());
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);
        $service = Service::parsePayload($payload);

        $serviceName = $service->getServiceName();
        $this->output->writeln("A new virtual host has been successfully added for <info>$serviceName</info>!");
        $this->getAentHelper()->spacer();

        return $service;
    }

    /**
     * @param Service $service
     * @return Service
     */
    private function newImageToBuild(Service $service): Service
    {
        $imageBuilderAentID = Manifest::getDependency(CommonDependencies::IMAGE_BUILDER_KEY);
        if (null === $imageBuilderAentID) {
            return $service;
        }

        $repliedPayloads = Aenthill::runJson($imageBuilderAentID, CommonEvents::NEW_IMAGE_EVENT, $service->imageJsonSerialize());
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);
        $dockerfileName = $payload['dockerfileName'];
        $this->getAentHelper()->spacer();

        $CIAentID = Manifest::getDependency(CommonDependencies::CI_KEY);
        if (null === $CIAentID) {
            return $service;
        }

        $serviceName = $service->getServiceName();

        $repliedPayloads = Aenthill::runJson($CIAentID, CommonEvents::NEW_BUILD_IMAGE_JOB_EVENT, [
            'serviceName' => $serviceName,
            'dockerfileName' => $dockerfileName,
        ]);
        $payload = \GuzzleHttp\json_decode($repliedPayloads[0], true);

        $dockerImageName = $payload['dockerImageName'];
        $service->setImage($dockerImageName);
        $service->removeAllBindVolumes();

        $this->getAentHelper()->spacer();
        $this->output->writeln("Service <info>$serviceName</info> is now using image <info>$dockerImageName</info>!");
        $this->getAentHelper()->spacer();

        return $service;
    }
}
