<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\Service\Service;
use TheAentMachine\AentDockerCompose\YamlTools\Exception\YamlToolsException;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;

class NewDockerServiceInfoEventCommand extends \TheAentMachine\AentDockerCompose\Aenthill\EventCommand
{

    protected function getEventName(): string
    {
        return EventEnum::NEW_DOCKER_SERVICE_INFO
    }

    protected function executeEvent(string $payload): int
    {
        $service = new Service();
        $service->parsePayload($payload ?? "");
        $formattedPayload = $service->serializeToDockerComposeService(false);
        $yml = Yaml::dump($formattedPayload, 256, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents(YamlTools::TMP_YAML_FILE, $yml);

        $dockerComposeFilePathnames = $this->getDockerComposePathnames();
        if (count($dockerComposeFilePathnames) == 1) {
            $toMerge = $dockerComposeFilePathnames;
        } else {
            $toMerge = $this->askMultiSelectQuestion($this->input, $this->output, $dockerComposeFilePathnames);
        }

        foreach ($toMerge as $file) {
            $exitCode = YamlTools::merge($file, YamlTools::TMP_YAML_FILE, $file);
            if ($exitCode !== 0) {
                throw new YamlToolsException();
            }
        }
        unlink(YamlTools::TMP_YAML_FILE);
        return 0;
    }
}
