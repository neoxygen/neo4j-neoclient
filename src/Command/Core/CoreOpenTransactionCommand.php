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
use Neoxygen\NeoClient\Request\Request;

class CoreOpenTransactionCommand extends AbstractCommand
{

    public function execute()
    {
        $request = new Request('POST', $this->getPath());

        $response = $this->httpClient->sendRequest($request);

        return $response;
    }

    public function getPath()
    {
        return $this->getBaseUrl() . '/db/data/transaction';
    }
}