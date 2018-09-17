<?php

namespace TheAentMachine\AentDockerCompose\Event;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use Symfony\Component\Filesystem\Filesystem;
use TheAentMachine\Aent\Event\Orchestrator\AbstractOrchestratorAddEvent;
use TheAentMachine\Aent\Payload\Bootstrap\BootstrapPayload;
use TheAentMachine\AentDockerCompose\Context\DockerComposeContext;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use function Safe\sprintf;
use function Safe\chown;
use function Safe\chgrp;

final class AddEvent extends AbstractOrchestratorAddEvent
{
    /** @var DockerComposeContext */
    private $context;

    /**
     * @return void
     */
    protected function before(): void
    {
        $this->output->writeln("\n👋 Hello! I'm the aent <info>Docker Compose</info> and I'm going to setup my configuration file.");
    }

    /**
     * @param BootstrapPayload $payload
     * @throws MissingEnvironmentVariableException
     * @throws StringsException
     * @throws FilesystemException
     */
    protected function process(BootstrapPayload $payload): void
    {
        $this->context = new DockerComposeContext($payload->getContext());
        $this->context->setDockerComposeFilename($this->getDockerComposeFilename());
        $this->output->writeln(sprintf("\n👌 Alright, I'm going to create <info>%s</info>!", $this->context->getDockerComposeFilename()));
        $this->createDockerComposeFile();
        // TODO Traefik
        // TODO CI
        // TODO builder?
    }

    /**
     * @return void
     * @throws StringsException
     */
    protected function after(): void
    {
        $this->output->writeln(
            sprintf(
                "\nI'm the aent <info>Docker Compose</info> and I've finished the setup of my configuration file for <info>%s</info> environment <info>%s</info>.",
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
        $text = "\nYour <info>Docker Compose</info> filename for <info>$environmentType</info> environment <info>$environmentName</info>";
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
        $fileSystem->dumpFile($dockerComposePath, "version: '3.7'");
        $dirInfo = new \SplFileInfo(\dirname($dockerComposePath));
        chown($dockerComposePath, $dirInfo->getOwner());
        chgrp($dockerComposePath, $dirInfo->getGroup());
    }
}
