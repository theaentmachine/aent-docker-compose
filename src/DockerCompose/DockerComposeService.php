<?php

namespace TheAentMachine\AentDockerCompose\DockerCompose;

use Nette\NotImplementedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\PheromoneEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Exception\ContainerProjectDirEnvVariableEmptyException;
use TheAentMachine\AentDockerCompose\Aenthill\LogLevelConfigurator;

class DockerComposeService
{
    /** @var LoggerInterface */
    private $log;

    /** @var DockerComposeFile[] */
    private $files;

    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }

    /**
     * @throws ContainerProjectDirEnvVariableEmptyException
     */
    private function seekFiles(): void
    {
        $containerProjectDir = getenv(PheromoneEnum::PHEROMONE_CONTAINER_PROJECT_DIR);
        if (empty($containerProjectDir)) {
            throw new ContainerProjectDirEnvVariableEmptyException();
        }

        $finder = new Finder();
        $dockerComposeFileFilter = function (\SplFileInfo $file) {
            return $file->isFile() && preg_match('/^docker-compose(.)*\.(yaml|yml)$/', $file->getFilename());
        };
        $finder->files()->filter($dockerComposeFileFilter)->in($containerProjectDir)->depth('== 0');

        if (!$finder->hasResults()) {
            $this->log->info("no docker-compose file found, let's create it");
            $this->createDockerComposeFile($containerProjectDir . '/docker-compose.yml');
            return;
        }

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $this->files[] = new DockerComposeFile($file);
            $this->log->info($file->getFilename() . ' has been found');
        }

        /*if (count($this->files) === 1) {
            $this->log->info($this->files[0]->getFilename() . ' has been found');
            return;
        }

        throw new NotImplementedException("multiple docker-compose files handling is not yet implemented");
        */
    }

    /**
     * @return string[]
     */
    public function getDockerComposePathnames(): array
    {
        if ($this->files === null) {
            $this->seekFiles();
        }
        $pathnames = array();
        foreach ($this->files as $file) {
            $pathnames[] = $file->getPathname();
        }
        return $pathnames;
    }

    /**
     * @param string $path
     */
    private function createDockerComposeFile(string $path): void
    {
        // TODO ask questions about version and so on!
        $fp = fopen($path, 'wb');
        fclose($fp);

        $file = new DockerComposeFile(new \SplFileInfo($path));
        $this->files[] = $file;
        $this->log->info($file->getFilename() . ' was successfully created!');
    }
}
