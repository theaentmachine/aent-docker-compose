<?php

namespace TheAentMachine\AentDockerCompose\YamlTools;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class YamlTools
{

    /**
     * Merge the content of $sourceFile into $destinationFile's one (overwritten)
     * @param string $destinationFile
     * @param string $sourceFile
     */
    public static function mergeTwoFiles(string $destinationFile, string $sourceFile): void
    {
        $files = [$destinationFile, $sourceFile];
        self::mergeSuccessive($files, $destinationFile);
    }

    /**
     * Given an array of yaml file pathnames, merge them from the last to the first
     * @param mixed[] $yamlFilePathnames
     * @param null|string $outputFile if null, dump the result to stdout
     */
    public static function mergeSuccessive(array $yamlFilePathnames, ?string $outputFile = null): void
    {
        $command = array('yaml-tools', 'merge', '-i');
        $command = array_merge($command, $yamlFilePathnames);
        if (null !== $outputFile) {
            $command[] = '-o';
            $command[] = $outputFile;
        }
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        $process->mustRun();
    }

    /**
     * Merge yaml content into one file
     * @param string $content
     * @param string $file
     */
    public static function mergeContentIntoFile(string $content, string $file): void
    {
        $fileSystem = new Filesystem();
        $tmpFile = $fileSystem->tempnam(sys_get_temp_dir(), 'yaml-tools-merge-');
        $fileSystem->dumpFile($tmpFile, $content);
        self::mergeTwoFiles($file, $tmpFile);
        $fileSystem->remove($tmpFile);
    }

    /**
     * Delete one yaml item given its path (e.g. key1 key2 0 key3) in the $inputFile, then write it into $outputFile (or stdout if empty)
     * Caution : this also deletes its preceding comments
     * @param string[] $pathToItem e.g. key1 key2 0 key3
     * @param string $file
     */
    public static function deleteYamlItem(array $pathToItem, string $file): void
    {
        $command = array('yaml-tools', 'delete');
        $command = array_merge($command, $pathToItem, [
            '-i', $file,
            '-o', $file,
        ]);
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        $process->mustRun();
    }

    /**
     * See https://github.com/thecodingmachine/yaml-tools#normalize-docker-compose
     * @param string $inputFile
     * @param string|null $outputFile
     */
    public static function normalizeDockerCompose(string $inputFile, ?string $outputFile = null): void
    {
        $command = array('yaml-tools', 'normalize-docker-compose', '-i', $inputFile);
        if (null !== $outputFile) {
            $command[] = '-o';
            $command[] = $outputFile;
        }
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        $process->mustRun();
    }
}
