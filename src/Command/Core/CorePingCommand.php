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

use Neoxygen\NeoClient\Command\AbstractCommand,
    Neoxygen\NeoClient\Request\Request;

class CorePingCommand extends AbstractCommand
{
    public function execute()
    {
        $request = $this->createRequest();
        $request->setMethod('HEAD');
        $request->setUrl($this->getConnection()->getBaseUrl());

        return $this->httpClient->sendRequest($request);
    }
}
