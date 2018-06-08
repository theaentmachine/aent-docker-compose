<?php

namespace TheAentMachine\AentDockerCompose\YamlTools;

use Symfony\Component\Process\Process;

class YamlTools
{
    const BINARY = 'yaml-tools';
    const TMP_YAML_FILE = '/tmp/tmp-yaml.yml';

    /**
     * Merge the content of $inputFile2 with $inputFile1's one, then write it into $outputFile (or stdout if empty)
     * @param string $inputFile1
     * @param string $inputFile2
     * @param string $outputFile
     * @return int
     */
    public static function merge(string $inputFile1, string $inputFile2, string $outputFile = ''): int
    {

        $command = array(YamlTools::BINARY, 'merge', '-i', $inputFile1, $inputFile2);
        if (!empty($outputFile)) {
            $command[] = '-o';
            $command[] = $outputFile;
        }
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        return $process->run();
    }

    /**
     * Delete one element of the $inputFile (e.g. foo.bar[2].baz), then write it into $outputFile (or stdout if empty)
     * @param string $elemToDelete
     * @param string $inputFile
     * @param string $outputFile
     * @return int
     */
    public static function delete(string $elemToDelete, string $inputFile, string $outputFile = ''): int
    {
        $command = array(YamlTools::BINARY, 'delete', $elemToDelete, '-i', $inputFile);
        if (!empty($outputFile)) {
            $command[] = '-o';
            $command[] = $outputFile;
        }
        $process = new Process($command);
        $process->enableOutput();
        $process->setTty(true);
        return $process->run();
    }
}
