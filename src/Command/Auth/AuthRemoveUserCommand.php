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

class AuthRemoveUserCommand extends AbstractCommand
{
    const METHOD = 'POST';

    const PATH = '/auth/remove-user';

    private $user;

    private $password;

    public function execute()
    {
        return $this->process(self::METHOD, self::PATH, $this->prepareBody(), $this->connection);
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    protected function prepareBody()
    {
        $userField = array(
            'user' => $this->user.':'.$this->password,
        );

        return $userField;
    }
}
