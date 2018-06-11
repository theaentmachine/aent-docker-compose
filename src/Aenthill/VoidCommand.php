<?php


namespace TheAentMachine\AentDockerCompose\Aenthill;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command that does nothing
 */
class VoidCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('void')
             ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Let's do nothing.
    }
}
