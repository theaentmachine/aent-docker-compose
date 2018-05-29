<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Output\OutputInterface;

class Utils
{
    /**
     * @param OutputInterface $output
     * @param string $message
     * @return string
     */
    public static function getAnswer(OutputInterface $output, string $message): string
    {
        $output->write($message);
        $str = trim(fgets(STDIN));
        return $str;
    }


    /**
     * @param OutputInterface $output
     * @param string $message
     * @return string[]
     */
    public static function getAnswerArray(OutputInterface $output, string $message): array
    {
        $array = array();
        $output->write($message);
        $str = trim(fgets(STDIN));
        while ($str != "") {
            array_push($array, $str);
            $output->write("another one? : ");
            $str = trim(fgets(STDIN));
        }
        return $array;
    }

    /**
     * Delete all key/value pairs with empty value by recursively using array_filter
     * @param array $input
     * @return mixed[] array
     */
    public static function arrayFilterRecursive(array $input): array
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = Utils::arrayFilterRecursive($value);
            }
        }
        return array_filter($input);
    }

    /**
     * @param string $payload
     * @return mixed[] array
     */
    public static function formatPayloadToDockerCompose(string $payload): array
    {
        $p = json_decode($payload, true);
        if (!$p) {
            echo ("error: invalid payload");
            return array();
        }

        $serviceName = $p[Constants::SERVICE_NAME_KEY] ?? "";
        if (empty($serviceName)) {
            echo ("error: empty ". Constants::SERVICE_NAME_KEY);
            return array();
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
                "services" => Utils::arrayFilterRecursive(array(
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
            echo ("error: empty service");
        }

        return array();
    }
}
