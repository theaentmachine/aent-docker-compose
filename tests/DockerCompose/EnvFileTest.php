<?php

namespace TheAentMachine\AentDockerCompose\DockerCompose;


use PHPUnit\Framework\TestCase;

class EnvFileTest extends TestCase
{

    public function testSet()
    {
        $tmpfname = tempnam(\sys_get_temp_dir(), 'env');

        $envFile = new EnvFile($tmpfname);

        $envFile->set('FOO', 'BAR', "hello\nworld", false); // chown() is not permitted in tests

        $content = \file_get_contents($tmpfname);
        $this->assertSame(<<<ENVFILE
# hello
# world
FOO=BAR

ENVFILE
            , $content);

        $envFile->set('BAZ', 'BAR', null, false);
        $content = \file_get_contents($tmpfname);
        $this->assertSame(<<<ENVFILE
# hello
# world
FOO=BAR
BAZ=BAR

ENVFILE
            , $content);

        $envFile->set('BAZ', 'BAR_MODIFIED', null, false);
        $content = \file_get_contents($tmpfname);
        $this->assertSame(<<<ENVFILE
# hello
# world
FOO=BAR
BAZ=BAR_MODIFIED

ENVFILE
            , $content);


    }
}
