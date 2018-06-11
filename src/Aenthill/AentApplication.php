<?php


namespace TheAentMachine\AentDockerCompose\Aenthill;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class AentApplication extends Application
{
    private $voidCommand;

    public function __construct()
    {
        parent::__construct();
        $this->voidCommand = new VoidCommand();
        $this->add($this->voidCommand);
    }

    /**
     * Overrides the Symfony "find" method to return a default command if no command is found.
     */
    public function find($name)
    {
        try {
            return parent::find($name);
        } catch (CommandNotFoundException $e) {
            return $this->voidCommand;
        }
    }
}
