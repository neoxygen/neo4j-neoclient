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

class CoreChangePasswordCommand extends AbstractCommand
{
    const METHOD = 'POST';

    const PATH = '/user/';

    protected $user;

    protected $password;

    protected $newPassword;

    public function setArguments($user, $password)
    {
        $this->user = (string) $user;
        $this->password = (string) $password;

        return $this;
    }

    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), $this->getBody(), $this->connection);
    }

    private function getPath()
    {
        return self::PATH.$this->user.'/password';
    }

    private function getBody()
    {
        $b = array(
            'password' => $this->password,
        );

        $body = json_encode($b);

        return $body;
    }
}
