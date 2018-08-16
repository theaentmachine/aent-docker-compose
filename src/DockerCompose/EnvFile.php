<?php


namespace TheAentMachine\AentDockerCompose\DockerCompose;

use Symfony\Component\Filesystem\Filesystem;

class EnvFile
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Adds or updates an environment variable.
     */
    public function set(string $key, string $value, string $comment = null, bool $setOwnership = true): void
    {
        $content = $this->getContent();
        if ($this->has($key)) {
            // Note: if the key is already in the file, comments are not modified.
            $content = \preg_replace("/^$key=.*/m", $key.'='.$value, $content);
        } else {
            $commentLines = \explode("\n", $comment ?? '');
            $commentLines = \array_map(function (string $line) {
                return '# '.$line;
            }, $commentLines);
            $comments = \implode("\n", $commentLines);
            if ($comment) {
                $content .= <<<ENVVAR
$comments

ENVVAR;
            }
            $content .= <<<ENVVAR
$key=$value

ENVVAR;
        }

        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->filePath, $content);

        if ($setOwnership) {
            $dirInfo = new \SplFileInfo(\dirname($this->filePath));
            chown($this->filePath, $dirInfo->getOwner());
            chgrp($this->filePath, $dirInfo->getGroup());
        }
    }

    private function has(string $envName): bool
    {
        $content = $this->getContent();
        return (bool) \preg_match("/^$envName=/m", $content);
    }

    private function getContent(): string
    {
        if (!\file_exists($this->filePath)) {
            return '';
        }
        $content = \file_get_contents($this->filePath);
        if ($content === false) {
            throw new \RuntimeException('Unable to read file '.$this->filePath);
        }
        return $content;
    }
}
