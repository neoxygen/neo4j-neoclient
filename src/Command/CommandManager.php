<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Command;

use Neoxygen\NeoClient\Exception\CommandException;

class CommandManager
{
    private $commands;

    public function __construct()
    {
        $this->commands = array();
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function registerCommand($commandAlias, CommandInterface $command)
    {
        if (array_key_exists($commandAlias, $this->commands)) {
            throw new CommandException(sprintf('The command "%s" is already registered', $commandAlias));
        }
        $this->commands[$commandAlias] = $command;
    }

    public function getCommand($commandAlias)
    {
        if (!array_key_exists($commandAlias, $this->commands)) {
            throw new CommandException(sprintf('The command "%s" is not registered', $commandAlias));
        }

        return $this->commands[$commandAlias];
    }

    public function hasCommand($commandAlias)
    {
        return array_key_exists($commandAlias, $this->commands);
    }
}
