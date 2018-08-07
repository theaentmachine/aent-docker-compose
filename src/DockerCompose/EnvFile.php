<?php


namespace TheAentMachine\AentDockerCompose\DockerCompose;

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
    public function set(string $key, string $value, string $comment = null): void
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

        $return = \file_put_contents($this->filePath, $content);
        if ($return === false) {
            throw new \RuntimeException('Unable to write file '.$this->filePath);
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
