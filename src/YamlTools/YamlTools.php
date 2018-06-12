<?php

namespace TheAentMachine\AentDockerCompose\YamlTools;

use Symfony\Component\Process\Process;

class YamlTools
{
    public const TMP_YAML_FILE = '/tmp/tmp.yml';

    /**
     * Merge the content of $inputFile2 with $inputFile1's one, then write it into $outputFile (or stdout if empty)
     */
    public static function merge(string $inputFile1, string $inputFile2, string $outputFile = ''): void
    {
        $command = array('yaml-tools', 'merge', '-i', $inputFile1, $inputFile2);
        if (!empty($outputFile)) {
            $command[] = '-o';
            $command[] = $outputFile;
        }
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        $process->mustRun();
    }

    /**
     * Delete one element of the $inputFile (e.g. foo.bar[2].baz), then write it into $outputFile (or stdout if empty)
     */
    public static function delete(string $elemToDelete, string $inputFile, string $outputFile = ''): void
    {
        $command = array('yaml-tools', 'delete', $elemToDelete, '-i', $inputFile);
        if (!empty($outputFile)) {
            $command[] = '-o';
            $command[] = $outputFile;
        }
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        $process->mustRun();
    }
}
