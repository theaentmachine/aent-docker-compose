<?php

namespace TheAentMachine\AentDockerCompose\Event;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use Symfony\Component\Filesystem\Filesystem;
use TheAentMachine\Aent\Context\Context;
use TheAentMachine\Aent\Event\Orchestrator\AbstractOrchestratorAddEvent;
use TheAentMachine\Aent\Payload\Bootstrap\BootstrapPayload;
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
    /** @var Context */
    private $bootstrapContext;

    /** @var DockerComposeContext */
    private $context;

    /** @var ColonyRegistry */
    private $reverseProxyServiceRegistry;

    /**
     * @return void
     * @throws ColonyRegistryException
     */
    protected function before(): void
    {
        $this->reverseProxyServiceRegistry = ColonyRegistry::reverseProxyServiceRegistry();
        $this->output->writeln("\n👋 Hello! I'm the aent <info>Docker Compose</info> and I'm going to setup a nice <info>docker-compose.yml</info> file.");
    }

    /**
     * @param BootstrapPayload $payload
     * @throws ColonyRegistryException
     * @throws FilesystemException
     * @throws MissingEnvironmentVariableException
     * @throws ServiceException
     * @throws StringsException
     */
    protected function process(BootstrapPayload $payload): void
    {
        $this->bootstrapContext = $payload->getContext();
        $this->context = new DockerComposeContext($this->bootstrapContext);
        $this->context->setDockerComposeFilename($this->getDockerComposeFilename());
        $this->output->writeln(sprintf("\n👌 Alright, I'm going to create the file <info>%s</info>!", $this->context->getDockerComposeFilename()));
        $this->createDockerComposeFile();
        $this->addReverseProxy();
        // TODO CI
        // TODO builder?
        $this->context->toMetadata();
    }

    /**
     * @return void
     * @throws StringsException
     */
    protected function after(): void
    {
        $this->output->writeln(
            sprintf(
                "\nI've finished the setup of the file <info>%s</info> for your <info>%s</info> environment <info>%s</info>. See you later!",
                $this->context->getDockerComposeFilename(),
                $this->context->getType(),
                $this->context->getName()
            )
        );
    }

    /**
     * @return string
     */
    private function getDockerComposeFilename(): string
    {
        $environmentType = $this->context->getType();
        $environmentName = $this->context->getName();
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
     * @throws ColonyRegistryException
     * @throws ServiceException
     * @throws StringsException
     */
    private function addReverseProxy(): void
    {
        $aent = $this->reverseProxyServiceRegistry->getAent(ColonyRegistry::TRAEFIK);
        Aenthill::register($aent->getImage(), DockerComposeContext::REVERSE_PROXY_SERVICE_DEPENDENCY_KEY, $this->bootstrapContext->toArray());
        $response = Aenthill::runJson(DockerComposeContext::REVERSE_PROXY_SERVICE_DEPENDENCY_KEY, 'ADD_REVERSE_PROXY', []);
        $payload = \GuzzleHttp\json_decode($response[0], true);
        $service = Service::parsePayload($payload);
        $serializedService = DockerComposeHelper::dockerComposeServiceSerialize($service);
        DockerComposeHelper::mergeContentInDockerComposeFile($serializedService, $this->context->getDockerComposeFilePath(), true);
    }
}
