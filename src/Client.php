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
    private $container;

    private $responseFormatter;

    private $lastResponse;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $formatterClass = $container->getParameter('response_formatter_class');
        $this->responseFormatter = $formatterClass;
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
        return $this->container;
    }
}
