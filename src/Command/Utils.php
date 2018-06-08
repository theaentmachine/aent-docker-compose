<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Utils
{
    /**
     * Delete all key/value pairs with empty value by recursively using array_filter
     * @param array $input
     * @return mixed[] array
     */
    public static function arrayFilterRec(array $input): array
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = Utils::arrayFilterRec($value);
            }
        }
        return array_filter($input);
    }

    /**
     * @param string|null $payload
     * @return mixed[] array
     */
    public static function parsePayload(?string $payload, OutputInterface $output): array
    {
        $p = json_decode($payload, true);
        if (!$p) {
            $output->writeln("   ⨯ payload error: invalid payload");
            exit(1);
        }

        $serviceName = $p[Cst::SERVICE_NAME_KEY] ?? "";
        if (empty($serviceName)) {
            $output->writeln("   ⨯ payload error: empty " . Cst::SERVICE_NAME_KEY);
            exit(1);
        }

        $service = $p['service'] ?? array();
        if (!empty($service)) {
            $image = $service['image'] ?? "";
            $dependsOn = $service['dependsOn'] ?? "";

            $ports = array();
            foreach ($service['ports'] ?? array() as $port) {
                $str = sprintf("%d:%d", $port['source'], $port['target']);
                $ports[] = $str;
            }
            $labels = array();
            foreach ($service['labels'] ?? array() as $label) {
                $str = $label['value'] ? sprintf("%s=%s", $label['key'], $label['value']) : $label['key'];
                $labels[] = $str;
            }
            $environments = array();
            foreach ($service['environments'] ?? array() as $env) {
                $str = $env['value'] ? sprintf("%s=%s", $env['key'], $env['value']) : $env['key'];
                $environments[] = $str;
            }

            $volumes = $p['volumes'] ?? array();
            $namedVolumes = array();
            foreach ($service['volumes'] ?? array() as $volume) {
                $type = $volume['type'] ?? "";
                $source = $volume['source'] ?? "";

                $formattedVolume = array(
                    "type" => $type,
                    "source" => $source,
                    "target" => $volume['target'] ?? "",
                    "read_only" => $volume['readOnly'] ?? "",
                );
                $volumes[] = $formattedVolume;

                // case it's a named volume
                if ($type === "volume") {
                    // for now we just add them without any option
                    $namedVolumes[$source] = null;
                }
            }

            $formattedPayload = array(
                "services" => Utils::arrayFilterRec(array(
                    $serviceName => array(
                        "image" => $image,
                        "depends_on" => $dependsOn,
                        "ports" => $ports,
                        "labels" => $labels,
                        "environments" => $environments,
                        "volumes" => $volumes,
                    ),
                )),
            );
            if (!empty($namedVolumes)) {
                $formattedPayload['volumes'] = $namedVolumes;
            }

            return $formattedPayload;
        } else {
            $output->writeln("  ⨯ payload error: empty " . Cst::SERVICE_KEY);
            exit(1);
        }

        exit(1);
    }

    /**
     * Run a process and return the exit code
     * @param mixed $cmd command line in a single string or an array of strings
     * @param OutputInterface $output
     * @return Process
     */
    public static function runAndGetProcess($cmd, OutputInterface $output): Process
    {
        if (!is_array($cmd)) {
            $cmd = explode(' ', $cmd);
        }

        $process = new Process($cmd);

        $process->start();
        foreach ($process as $type => $buffer) {
            $output->write($buffer);
        }

        return $process;
    }
}
