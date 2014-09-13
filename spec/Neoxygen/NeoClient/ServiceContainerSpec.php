<?php

namespace spec\Neoxygen\NeoClient;

use Neoxygen\NeoClient\Connection\ConnectionManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Neoxygen\NeoClient\Command\SimpleCommand,
    Neoxygen\NeoClient\Command\CommandManager,
    Neoxygen\NeoClient\Connection\Connection;

class ServiceContainerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\NeoClient\ServiceContainer');
    }

    function it_should_have_a_service_container_on_construct()
    {
        $this->getServiceContainer()->shouldHaveType('Symfony\Component\DependencyInjection\Container');
    }

    function it_should_not_have_loaded_config_by_default()
    {
        $this->getLoadedConfig()->shouldBeNull();
    }

    function it_should_load_configuration_from_file()
    {
        $this->loadConfiguration(__DIR__.'/default_config.yml')->shouldHaveType('Neoxygen\NeoClient\ServiceContainer');
    }

    function it_should_build_the_container()
    {
        $this->loadConfiguration(__DIR__.'/default_config.yml');
        $this->build();
        $this->getServiceContainer()->isFrozen()->shouldReturn(true);
    }

    function it_should_get_container_definitions_from_extension()
    {
        $this->loadConfiguration(__DIR__.'/default_config.yml');
        $this->build();
        $this->getServiceContainer()->get('neoclient.connection_manager')->shouldHaveType('Neoxygen\NeoClient\Connection\ConnectionManager');
    }

    function it_should_return_a_specified_service()
    {
        $this->loadConfiguration($this->getConfigPath());
        $this->build();
        $this->getConnectionManager()->shouldHaveType('Neoxygen\NeoClient\Connection\ConnectionManager');
    }

    function it_should_return_the_command_manager()
    {
        $this->loadConfiguration($this->getConfigPath());
        $this->build();
        $this->getCommandManager()->shouldHaveType('Neoxygen\NeoClient\Command\CommandManager');
    }

    function it_should_invoke_a_command(CommandManager $manager, SimpleCommand $command)
    {
        $this->loadConfiguration($this->getConfigPath());
        $this->build();
        $manager->getCommand(Argument::any())->willReturn($command);
        $this->invoke('simple_command')->shouldHaveType('Neoxygen\NeoClient\Command\SimpleCommand');
    }

    function it_should_on_invoke_add_the_connection_to_the_command(CommandManager $manager, SimpleCommand $command, Connection $connection)
    {
        $this->loadConfiguration($this->getConfigPath());
        $this->build();
        $manager->getCommand(Argument::any())->willReturn($command);
        $command->getConnection()->willReturn($connection);
        $this->invoke('simple_command')->getConnection()->getAlias()->shouldNotBeNull();
    }

    private function getConfigPath()
    {
        return __DIR__.'/default_config.yml';
    }
}
