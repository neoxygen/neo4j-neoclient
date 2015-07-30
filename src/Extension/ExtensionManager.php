<?php

namespace Neoxygen\NeoClient\Extension;

use Neoxygen\NeoClient\Command\CommandManager;
use Neoxygen\NeoClient\Formatter\ResponseFormatterManager;
use Neoxygen\NeoClient\Connection\ConnectionManager;

class ExtensionManager
{
    private $extensions = [];

    private $commandManager;

    private $connectionManager;

    private $execs = [];

    private $responseFormatter;

    private $autoFormatResponse;

    private $resultDataContent;

    protected $newFormatModeEnabled;

    public function __construct(CommandManager $commandManager, ConnectionManager $connectionManager, ResponseFormatterManager $responseFormatter, $autoFormatResponse, $resultDataContent, $newFormatModeEnabled)
    {
        $this->commandManager = $commandManager;
        $this->connectionManager = $connectionManager;
        $this->responseFormatter = $responseFormatter;
        $this->autoFormatResponse = $autoFormatResponse;
        $this->resultDataContent = $resultDataContent;
        $this->newFormatModeEnabled = $newFormatModeEnabled;
    }

    public function addExtension($extension)
    {
        array_unshift(
            $this->extensions,
            new $extension(
                $this->commandManager,
                $this->connectionManager,
                $this->responseFormatter,
                $this->autoFormatResponse,
                $this->resultDataContent,
                $this->newFormatModeEnabled)
        );
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
