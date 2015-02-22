<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Command\Auth;

use Neoxygen\NeoClient\Command\AbstractCommand;

class AuthListUsersCommand extends AbstractCommand
{
    const METHOD = 'GET';

    const PATH = '/auth/list';

    public function execute()
    {
        return $this->process(self::METHOD, self::PATH, null, $this->connection);
    }
}
