<?php

namespace Neoxygen\NeoClient\Tests\Command;

use Neoxygen\NeoClient\Command\SimpleCommand;
use Neoxygen\NeoClient\HttpClient\GuzzleHttpClient;
use Neoxygen\NeoClient\Tests\NeoClientTestCase;

class CommandManagerTest extends NeoClientTestCase
{
    public function testManager()
    {
        $sc = $this->build();
        $cm = $sc->getCommandManager();

        $this->assertNotEmpty($cm->getCommands());
    }

    /**
     * @expectedException Neoxygen\NeoClient\Exception\CommandException
     */
    public function testExceptionWhenCommandAliasAlreadyExist()
    {
        $sc = $this->build();
        $cm = $sc->getCommandManager();

        $command = new SimpleCommand(new GuzzleHttpClient());
        $cm->registerCommand('simple_command', $command);

    }

    /**
     * @expectedException Neoxygen\NeoClient\Exception\CommandException
     */
    public function testExceptionCommandNotExist()
    {
        $sc = $this->build();
        $cm = $sc->getCommandManager();

        $cm->getCommand('BadCommand');
    }

    public function testHasCommand()
    {
        $sc = $this->build();
        $cm = $sc->getCommandManager();

        $this->assertTrue($cm->hasCommand('simple_command'));
    }
}