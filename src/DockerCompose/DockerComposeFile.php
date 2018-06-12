<?php

namespace TheAentMachine\AentDockerCompose\DockerCompose;

class DockerComposeFile
{
    /** @var \SplFileInfo */
    private $file;

    /**
     * DockerComposeFile constructor.
     * @param \SplFileInfo $file
     */
    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->file->getFilename();
    }

    /**
     * @return string
     */
    public function getPathname(): string
    {
        return $this->file->getPathname();
    }
}
