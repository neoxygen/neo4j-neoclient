<?php

namespace Neoxygen\NeoClient\Extension;

use Neoxygen\NeoClient\Connection\Connection;
use Neoxygen\NeoClient\Extension\NeoClientExtensionInterface,
    Neoxygen\NeoClient\Command\CommandManager,
    Neoxygen\NeoClient\Formatter\ResponseFormatterManager,
    Neoxygen\NeoClient\Exception\Neo4jException,
    Neoxygen\NeoClient\Connection\ConnectionManager;

abstract class AbstractExtension implements NeoClientExtensionInterface
{
    protected $commandManager;

    protected $connectionManager;

    protected $responseFormatter;

    protected $autoFormatResponse;

    protected $resultDataContent;

    public function __construct(
        CommandManager $commandManager,
        ConnectionManager $connectionManager,
        ResponseFormatterManager $responseFormatter,
        $autoFormatResponse,
        $resultDataContent)
    {
        $this->commandManager = $commandManager;
        $this->connectionManager = $connectionManager;
        $this->responseFormatter = $responseFormatter->getResponseFormatter();
        $this->autoFormatResponse = $autoFormatResponse;
        $this->resultDataContent = $resultDataContent;
    }

    /**
     * @param $commandAlias
     * @param  null                                        $connectionAlias
     * @return \Neoxygen\NeoClient\Command\AbstractCommand
     */
    public function invoke($commandAlias, $connectionAlias = null)
    {
        $command = $this->commandManager->getCommand($commandAlias);
        $command->setConnection($connectionAlias);

        return $command;
    }

    /**
     * @param  mixed                                  $response
     * @return \Neoxygen\NeoClient\Formatter\Response
     */
    public function formatResponse($response)
    {
        $formatted = $this->responseFormatter->format($response);

        return $formatted;
    }

    /**
     * @param  mixed                                               $response
     * @return string|array|\Neoxygen\NeoClient\Formatter\Response
     * @throws Neo4jException
     */
    public function handleHttpResponse($response)
    {
        $this->checkResponseErrors($response);
        if ($this->autoFormatResponse) {
            return $this->formatResponse($response);
        } else {
            return $response;
        }
    }

    public function checkResponseErrors($response)
    {
        if (isset($response['errors']) && !empty($response['errors'])) {
            throw new Neo4jException(sprintf('Neo4j Exception with code "%s" and message "%s"', $response['errors'][0]['code'], $response['errors'][0]['message']));
        }
    }

    protected function getWriteConnection()
    {
        return $this->connectionManager->getWriteConnection();
    }

    protected function getReadConnection()
    {
        return $this->connectionManager->getReadConnection();
    }
}
