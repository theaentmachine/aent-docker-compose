<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\Aenthill\Enum\EventEnum;
use TheAentMachine\AentDockerCompose\YamlTools\Exception\YamlToolsException;
use TheAentMachine\AentDockerCompose\YamlTools\YamlTools;

class NewDockerServiceInfoEventCommand extends EventCommand
{
    protected function configure()
    {
        $this->setName(EventEnum::NEW_DOCKER_SERVICE_INFO);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws YamlToolsException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formattedPayload = Utils::parsePayload($this->payload, $output);
        $yml = Yaml::dump($formattedPayload, 256, 4, Yaml::DUMP_OBJECT_AS_MAP);
        file_put_contents(YamlTools::TMP_YAML_FILE, $yml);

        $dockerComposeFilePathnames = $this->getDockerComposePathnames();
        $toMerge = $this->askMultiSelectQuestion($input, $output, $dockerComposeFilePathnames);

        if (!empty($toMerge)) {
            foreach ($toMerge as $file) {
                $exitCode = YamlTools::merge($file, YamlTools::TMP_YAML_FILE, $file);
                if ($exitCode !== 0) {
                    throw new YamlToolsException();
                }
            }
        }
        unlink(YamlTools::TMP_YAML_FILE);
        return 0;
    }
}
