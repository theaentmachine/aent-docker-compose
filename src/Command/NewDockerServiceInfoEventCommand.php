<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;
use TheAentMachine\JsonEventCommand;
use TheAentMachine\Service\Enum\VolumeTypeEnum;
use TheAentMachine\Service\Environment\EnvVariable;
use TheAentMachine\Service\Service;
use TheAentMachine\Service\Volume\Volume;

class NewDockerServiceInfoEventCommand extends JsonEventCommand
{

    protected function getEventName(): string
    {
        return EventEnum::NEW_DOCKER_SERVICE_INFO;
    }

    protected function executeJsonEvent(array $payload): void
    {
        $service = Service::parsePayload($payload);
        $formattedPayload = $this->dockerComposeServiceSerialize($service);

        // $this->log->debug(json_encode($formattedPayload, JSON_PRETTY_PRINT));

        $yml = Yaml::dump($formattedPayload, 256, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents(YamlTools::TMP_YAML_FILE, $yml);

        $dockerComposeService = new DockerComposeService($this->log);
        $dockerComposeFilePathnames = $dockerComposeService->getDockerComposePathnames();
        if (count($dockerComposeFilePathnames) === 1) {
            $toMerge = $dockerComposeFilePathnames;
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please choose the docker-compose file(s) in which the service will be added (e.g. 0,1) : ',
                $dockerComposeFilePathnames,
                null
            );
            $question->setMultiselect(true);

            $toMerge = $helper->ask($this->input, $this->output, $question);
        }

        foreach ($toMerge as $file) {
            YamlTools::merge($file, YamlTools::TMP_YAML_FILE, $file);
        }

        unlink(YamlTools::TMP_YAML_FILE);
    }

    /**
     * @param Service $service
     * @return mixed[]
     */
    public function dockerComposeServiceSerialize(Service $service): array
    {
        $portMap = function (array $port): string {
            return $port['source'] . ':' . $port['target'];
        };
        $labelMap = function (string $key, array $label): string {
            return $key . '=' . $label['value'];
        };
        $envMap = function (string $key, EnvVariable $env): array {
            return [$key => $env->getValue()];
        };
        $jsonSerializeMap = function (\JsonSerializable $obj): array {
            return $obj->jsonSerialize();
        };
        $dockerService = [
            'services' => [
                $service->getServiceName() => array_filter([
                    'image' => $service->getImage(),
                    'command' => $service->getCommand(),
                    'depends_on' => $service->getDependsOn(),
                    'ports' => array_map($portMap, $service->getPorts()),
                    'labels' => array_map($labelMap, array_keys($service->getLabels()), $service->getLabels()),
                    'environment' => array_map($envMap, array_keys($service->getEnvironment()), $service->getEnvironment()),
                    'volumes' => array_map($jsonSerializeMap, $service->getVolumes()),
                ]),
            ],
        ];
        $namedVolumes = array();
        /** @var Volume $volume */
        foreach ($service->getVolumes() as $volume) {
            if ($volume->getType() === VolumeTypeEnum::NAMED_VOLUME) {
                // for now we just add them without any option
                $namedVolumes[$volume->getSource()] = null;
            }
        }
        if (!empty($namedVolumes)) {
            $dockerService['volumes'] = $namedVolumes;
        }
        return $dockerService;
    }

    /**
     * Delete all key/value pairs with empty value by recursively using array_filter
     * @param array $input
     * @return mixed[] array
     */
    private static function arrayFilterRec(array $input): array
    {
        foreach ($input as &$value) {
            if (\is_array($value)) {
                $value = self::arrayFilterRec($value);
            }
        }
        return array_filter($input);
    }
}
