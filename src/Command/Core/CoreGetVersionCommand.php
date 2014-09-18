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

class CoreGetVersionCommand extends AbstractCommand
{
    const METHOD = 'GET';

    const PATH = '/db/data';

    public function execute()
    {
        $data = $this->httpClient->send(self::METHOD, self::PATH, null, $this->connection);

        if (!is_array($data)) {
            $data = json_decode($data, true);
        }

        return $data['neo4j_version'];
    }
}
