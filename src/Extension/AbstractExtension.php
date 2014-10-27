<?php

namespace Neoxygen\NeoClient\Extension;

use Neoxygen\NeoClient\Extension\NeoClientExtensionInterface,
    Neoxygen\NeoClient\Command\CommandManager,
    Neoxygen\NeoClient\Formatter\ResponseFormatterManager,
    Neoxygen\NeoClient\Exception\Neo4jException;

abstract class AbstractExtension implements NeoClientExtensionInterface
{
    protected $commandManager;

    protected $responseFormatter;

    public function __construct(
        CommandManager $commandManager,
        ResponseFormatterManager $responseFormatter)
    {
        $this->commandManager = $commandManager;
        $this->responseFormatter = $responseFormatter->getResponseFormatter();
    }

    public function invoke($commandAlias, $connectionAlias = null)
    {
        $command = $this->commandManager->getCommand($commandAlias);
        $command->setConnection($connectionAlias);

        return $command;
    }

    /**
     * @param mixed $response
     * @return \Neoxygen\NeoClient\Formatter\Response
     */
    public function formatResponse($response)
    {
        $formatted = $this->responseFormatter->format($response);

        if ($formatted->hasErrors()){
            $errors = $formatted->getErrors();
            throw new Neo4jException(sprintf('NeoClient Exception with code "%s" and message "%s"', $errors['code'], $errors['message']));
        }

        return $formatted;
    }
}
