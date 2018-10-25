<?php

namespace TheAentMachine\AentDockerCompose\Helper;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\PcreException;
use Symfony\Component\Filesystem\Filesystem;
use function \Safe\chown;
use function \Safe\chgrp;
use function \Safe\preg_match;
use function \Safe\file_get_contents;

final class EnvFileHelper
{
    /** @var string */
    private $filePath;

    /**
     * EnvFileHelper constructor.
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Adds or updates an environment variable.
     * @param string $key
     * @param string $value
     * @param string|null $comment
     * @param bool $setOwnership
     * @throws PcreException
     * @throws FilesystemException
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
                return '# '. $line;
            }, $commentLines);
            $comments = \implode("\n", $commentLines);
            if ($comment) {
                $content .= "\n$comments";
            }
            $content .= "\n$key=value";
            /*if ($comment) {
                $content .= <<<ENVVAR
$comments
ENVVAR;
            }
            $content .= <<<ENVVAR
$key=$value
ENVVAR;*/
        }
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($this->filePath, $content ?? '');
        if ($setOwnership) {
            $dirInfo = new \SplFileInfo(\dirname($this->filePath));
            chown($this->filePath, $dirInfo->getOwner());
            chgrp($this->filePath, $dirInfo->getGroup());
        }
    }

    /**
     * @param string $envName
     * @return bool
     * @throws PcreException
     * @throws FilesystemException
     */
    private function has(string $envName): bool
    {
        $content = $this->getContent();
        return (bool) preg_match("/^$envName=/m", $content);
    }

    /**
     * @return string
     * @throws FilesystemException
     */
    private function getContent(): string
    {
        if (!\file_exists($this->filePath)) {
            return '';
        }
        $content = file_get_contents($this->filePath);
        return $content;
    }
}
