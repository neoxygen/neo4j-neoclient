<?php

namespace Neoxygen\NeoClient\Extension;

use GraphAware\NeoClient\Formatter\ResponseFormattingService;
use Neoxygen\NeoClient\Request\Response;
use Neoxygen\NeoClient\Command\CommandManager;
use Neoxygen\NeoClient\Formatter\ResponseFormatterManager;
use Neoxygen\NeoClient\Exception\Neo4jException;
use Neoxygen\NeoClient\Connection\ConnectionManager;

abstract class AbstractExtension implements NeoClientExtensionInterface
{
    const WRITE_QUERY = 'WRITE';

    const READ_QUERY = 'READ';

    protected $commandManager;

    protected $connectionManager;

    protected $responseFormatter;

    protected $autoFormatResponse;

    protected $resultDataContent;

    public $newFormatModeEnabled;

    /**
     * @var \GraphAware\NeoClient\Formatter\ResponseFormattingService
     */
    public $newFormattingService;

    public function __construct(
        CommandManager $commandManager,
        ConnectionManager $connectionManager,
        ResponseFormatterManager $responseFormatter,
        $autoFormatResponse,
        $resultDataContent,
        $newFormatModeEnabled)
    {
        $this->commandManager = $commandManager;
        $this->connectionManager = $connectionManager;
        $this->responseFormatter = $responseFormatter->getResponseFormatter();
        $this->autoFormatResponse = $autoFormatResponse;
        $this->resultDataContent = $resultDataContent;
        $this->newFormatModeEnabled = $newFormatModeEnabled;
        if ($this->newFormatModeEnabled) {
            $this->newFormattingService = new ResponseFormattingService();
        }
    }

    /**
     * @param string      $commandAlias
     * @param null|string $connectionAlias
     *
     * @return \Neoxygen\NeoClient\Command\AbstractCommand
     */
    public function invoke($commandAlias, $connectionAlias = null)
    {
        $command = $this->commandManager->getCommand($commandAlias);
        $command->setConnection($connectionAlias);

        return $command;
    }

    /**
     * @param mixed $response
     *
     * @return \Neoxygen\NeoClient\Formatter\Response
     */
    public function formatResponse($response)
    {
        $formatted = $this->responseFormatter->format($response);

        return $formatted;
    }

    /**
     * @param Response $response
     *
     * @return string|array|\Neoxygen\NeoClient\Formatter\Response
     *
     * @throws Neo4jException
     */
    public function handleHttpResponse(Response $response)
    {
        if ($this->newFormatModeEnabled) {
            $newResponse = $this->newFormattingService->formatResponse($response->getRaw());

            if ($newResponse->hasError()) {
                $error = $newResponse->getError();
                throw new Neo4jException(sprintf(
                    $error->getCode(),
                    $error->getMessage(),
                    Neo4jException::fromCode($error->getCode())
                ));
            }

            return $newResponse;
        }

        $this->checkResponseErrors($response->getBody());
        if ($this->autoFormatResponse) {
            $formatted = $this->formatResponse($response->getBody());
            $response->setResult($formatted->getResult());
            $response->setRows($formatted->geRows());
        }

        return $response;
    }

    public function checkResponseErrors($response)
    {
        if (isset($response['errors']) && !empty($response['errors'])) {
            throw new Neo4jException(
                sprintf(
                    'Neo4j Exception with code "%s" and message "%s"',
                    $response['errors'][0]['code'],
                    $response['errors'][0]['message']),
                        Neo4jException::fromCode($response['errors'][0]['code'])
            );
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
