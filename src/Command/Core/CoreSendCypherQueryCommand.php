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

namespace Neoxygen\NeoClient\Command\Core;

use Neoxygen\NeoClient\Command\AbstractCommand;
use Neoxygen\NeoClient\NeoClient;
use Neoxygen\NeoClient\Request\Request;

class CoreSendCypherQueryCommand extends AbstractCommand
{
    public $query;

    public $parameters;

    public $resultDataContents;

    public function setArguments($query, array $parameters = array(), array $resultDataContents = array())
    {
        $this->query = $query;
        $this->parameters = $parameters;
        $this->resultDataContents = $resultDataContents;

        return $this;
    }

    public function execute()
    {
        $body = $this->prepareBody();
        $request = new Request('POST', $this->getPath(), $body);

        //NeoClient::log('debug', sprintf('Sending query %s', $body));
        $response = $this->httpClient->sendRequest($request);
        //NeoClient::log('debug', sprintf('Query sent with the following response %s', json_encode($response)));
        return $response;
    }

    public function prepareBody()
    {
        $statement = array();
        $statement['statement'] = $this->query;
        if (!empty($this->parameters)) {
            $statement['parameters'] = $this->parameters;
        }
        $body = array(
            'statements' => array(
                $statement
            )
        );

        return json_encode($body);
    }

    public function getPath()
    {
        return $this->getBaseUrl() . '/db/data/transaction/commit';
    }
}
