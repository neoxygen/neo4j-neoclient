<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Command\Core;

use Neoxygen\NeoClient\Command\AbstractCommand;
use Neoxygen\NeoClient\Exception\HttpException;

class CoreGetHAMasterCommand extends AbstractCommand
{
    const METHOD = 'GET';

    const PATH = '/db/manage/server/ha/master';

    public function execute()
    {
        try {
            return $this->process(self::METHOD, self::PATH, null, $this->connection, array(), 'HA_DETECTION');
        } catch (HttpException $e) {
            return false;
        }
    }
}
