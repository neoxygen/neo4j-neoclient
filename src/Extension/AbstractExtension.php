<?php

namespace Neoxygen\NeoClient\Extension;

use Neoxygen\NeoClient\Extension\NeoClientExtensionInterface,
    Neoxygen\NeoClient\Command\CommandManager;

abstract class AbstractExtension implements NeoClientExtensionInterface
{
    protected $commandManager;

    public function __construct(CommandManager $commandManager)
    {
        $this->commandManager = $commandManager;
    }

    public function invoke($commandAlias, $connectionAlias = null)
    {
        $command = $this->commandManager->getCommand($commandAlias);
        $command->setConnection($connectionAlias);

        return $command;
    }
}
