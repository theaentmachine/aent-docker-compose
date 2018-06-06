<?php
namespace TheAentMachine\AentDockerCompose\DockerCompose;

use Nette\NotImplementedException;
use Symfony\Component\Finder\Finder;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\PheromoneEnum;
use TheAentMachine\AentDockerCompose\Aenthill\Log;
use TheAentMachine\AentDockerCompose\Aenthill\Exception\ContainerProjectDirEnvVariableEmptyException;

class DockerCompose
{
    /** @var Log */
    private $log;

    /** @var DockerComposeFile[] */
    private $files;

    /**
     * DockerCompose constructor.
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
        $this->files = [];
    }

    /**
     * @throws ContainerProjectDirEnvVariableEmptyException
     */
    public function seekFiles(): void
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
            $this->log->infoln("no docker-compose file found, let's create it");
            $this->createDockerComposeFile($containerProjectDir . '/docker-compose.yml');
            return;
        }

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $this->files[] = new DockerComposeFile($file);
        }

        if (count($this->files) === 1) {
            $this->log->infoln($this->files[0]->getFilename() . ' has been found');
            return;
        }

        throw new NotImplementedException("multiple docker-compose files handling is not yet implemented");
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
        $this->log->infoln($file->getFilename() . ' was successfully created!');
    }
}
