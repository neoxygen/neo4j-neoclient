<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\HttpClient;

use Neoxygen\NeoClient\Request\Request;

interface HttpClientInterface
{
    /**
     * @param \Neoxygen\NeoClient\Request\Request $request
     *
     * @return \Neoxygen\NeoClient\Request\Response
     */
    public function sendRequest(Request $request);
}
