<?php

namespace TheAentMachine\AentDockerCompose\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use TheAentMachine\AentDockerCompose\Aenthill\Log;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerCompose;

abstract class EventCommand extends Command
{
    /** @var Log */
    protected $log;

    /** @var null|string */
    protected $payload;

    /** @var DockerCompose */
    protected $dockerCompose;

    /**
     * @param Log $log
     */
    public function setLog(Log $log): void
    {
        $this->log = $log;
    }

    /**
     * @param null|string $payload
     */
    public function setPayload(?string $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @param DockerCompose $dockerCompose
     */
    public function setDockerCompose(DockerCompose $dockerCompose): void
    {
        $this->dockerCompose = $dockerCompose;
    }

    /**
     * @return string[]
     */
    public function getDockerComposePathnames(): array
    {
        return $this->dockerCompose->getDockerComposePathnames();
    }

    /**
     * Asks a multiselect question, expects at least 1 choice
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string[] $choices
     * @return string[]
     */
    public function askMultiSelectQuestion(InputInterface $input, OutputInterface $output, array $choices) : array
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            "please select one or more response (e.g. 0,1) : ",
            $choices,
            null
        );
        $question->setMultiselect(true);
        // Empty answer are not allowed
        return $helper->ask($input, $output, $question);
    }
}
