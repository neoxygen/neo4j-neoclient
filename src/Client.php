<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Neoxygen\NeoClient;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Neoxygen\NeoClient\Exception\Neo4jException;

/**
 * @method sendCypherQuery($query, array $parameters = array(), $conn = null, array $resultDataContents = array())
 */

class Client
{
    private $serviceContainer;

    private $responseFormatter;

    private $lastResponse;

    public function __construct(ContainerInterface $container)
    {
        $this->serviceContainer = $container;
        $formatterClass = $container->getParameter('response_formatter_class');
        $this->responseFormatter = $formatterClass;
    }

    /**
     * Returns the ConnectionManager Service
     *
     * @return Neoxygen\NeoClient\Connection\ConnectionManager
     */
    public function getConnectionManager()
    {
        return $this->serviceContainer->get('neoclient.connection_manager');
    }

    /**
     * Returns the connection bound to the alias, or the default connection if no alias is provided
     *
     * @param  string|null                              $alias
     * @return Neoxygen\NeoClient\Connection\Connection The connection with alias "$alias"
     */
    public function getConnection($alias = null)
    {
        return $this->getConnectionManager()->getConnection($alias);
    }

    /**
     * Returns the CommandManager Service
     *
     * @return Neoxygen\NeoClient\Command\CommandManager
     */
    public function getCommandManager()
    {
        return $this->serviceContainer->get('neoclient.command_manager');
    }

    public function __call($method, $attributes)
    {
        $extManager = $this->getServiceContainer()->get('neoclient.extension_manager');

        $response = $extManager->execute($method, $attributes);

        $formatter = new $this->responseFormatter();

        $responseObject = $formatter->format($response);

        if ($responseObject->hasErrors()){
            throw new Neo4jException(sprintf('Neo4j Http Transaction Exception with code "%s" and with message "%s"', $responseObject->getErrors()['code'], $responseObject->getErrors()['message']));
        }

        $this->lastResponse = $responseObject;
        unset($formatter);

        return $this;
    }

    public function getResponse()
    {
        return $this->lastResponse->getResponse();
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function getResults()
    {
        return $this->getLastResponse()->getResults();
    }

    public function getResult()
    {
        return $this->getLastResponse()->getResult();
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}
