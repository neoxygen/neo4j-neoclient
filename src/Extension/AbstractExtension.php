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

    protected $autoFormatResponse;

    public function __construct(
        CommandManager $commandManager,
        ResponseFormatterManager $responseFormatter,
        $autoFormatResponse)
    {
        $this->commandManager = $commandManager;
        $this->responseFormatter = $responseFormatter->getResponseFormatter();
        $this->autoFormatResponse = $autoFormatResponse;
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

        return $formatted;
    }

    /**
     * @param mixed $response
     * @return string|array|\Neoxygen\NeoClient\Formatter\Response
     * @throws Neo4jException
     */
    public function handleHttpResponse($response)
    {
        $this->checkResponseErrors($response);
        if ($this->autoFormatResponse){
            return $this->formatResponse($response);
        } else {
            return $response;
        }
    }

    public function checkResponseErrors($response)
    {
        if (isset($response['errors']) && !empty($response['errors'])){
            throw new Neo4jException(sprintf('Neo4j Exception with code "%s" and message "%s"', $response['errors'][0]['code'], $response['errors'][0]['message']));
        }
    }
}
