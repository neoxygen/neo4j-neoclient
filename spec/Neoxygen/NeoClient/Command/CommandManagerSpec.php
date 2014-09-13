<?php

namespace spec\Neoxygen\NeoClient\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Neoxygen\NeoClient\Command\SimpleCommand;

class CommandManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\NeoClient\Command\CommandManager');
    }

    function it_should_have_an_empty_array_of_commands_by_default()
    {
        $this->getCommands()->shouldHaveCount(0);
    }

    function it_should_register_new_commands(SimpleCommand $command)
    {
        $this->registerCommand('neo.simple_command', $command);
        $this->getCommands()->shouldHaveCount(1);
    }

    function it_should_throw_error_if_command_is_already_registered(SimpleCommand $command)
    {
        $this->registerCommand('neo.test', $command);
        $this->shouldThrow('Neoxygen\NeoClient\Exception\CommandException')->duringRegisterCommand('neo.test', $command);
    }

    function it_should_return_a_command_by_alias(SimpleCommand $command)
    {
        $this->registerCommand('neo.test', $command);
        $this->getCommand('neo.test')->shouldHaveType('Neoxygen\NeoClient\Command\SimpleCommand');
    }

    function it_should_throw_error_if_command_does_not_exist()
    {
        $this->shouldThrow('Neoxygen\NeoClient\Exception\CommandException')->duringGetCommand('neo.test');
    }

    function it_should_return_bool_if_command_exist(SimpleCommand $command)
    {
        $this->hasCommand('neo.test')->shouldReturn(false);
        $this->registerCommand('neo.test', $command);
        $this->hasCommand('neo.test')->shouldReturn(true);
    }

}
