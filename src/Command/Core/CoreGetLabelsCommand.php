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

class CoreGetLabelsCommand extends AbstractCommand
{
    public function execute()
    {
        $request = new Request('GET', $this->getPath());

        return $this->httpClient->sendRequest($request);
    }

    private function getPath()
    {
        return $this->getConnection()->getBaseUrl() . '/db/data/labels';
    }
}
