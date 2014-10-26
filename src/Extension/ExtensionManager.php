<?php

namespace Neoxygen\NeoClient\Extension;

use Neoxygen\NeoClient\Command\CommandManager,
    Neoxygen\NeoClient\Extension\NeoClientExtensionInterface;

class ExtensionManager
{
    private $extensions = [];

    private $commandManager;

    private $execs = [];

    public function __construct(CommandManager $commandManager)
    {
        $this->commandManager = $commandManager;
    }

    public function addExtension($extension)
    {
        array_unshift($this->extensions, new $extension($this->commandManager));
    }

    public function execute($method, $attributes = array())
    {
        return call_user_func_array($this->getExecution($method), $attributes);
    }

    public function getExecution($method)
    {
        if (isset($this->execs[$method], $this->execs)) {
            return $this->execs[$method];
        }
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $method)) {
                $this->execs[$method] = array($extension, $method);

                return $this->execs[$method];
            }
        }
        throw new \InvalidArgumentException(sprintf('The method "%s" does not exist', $method));
    }

    public function getRegisteredExtensions()
    {
        return $this->extensions;
    }
}
