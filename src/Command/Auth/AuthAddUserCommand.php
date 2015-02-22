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

class AuthAddUserCommand extends AbstractCommand
{
    const METHOD = 'POST';

    const PATH = '/auth/add-user-';

    private $readOnly = false;

    private $user;

    private $password;

    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), $this->prepareBody(), $this->connection);
    }

    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;

        return $this;
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

    protected function getPath()
    {
        $mode = true === $this->readOnly ? 'ro' : 'rw';
        $path = self::PATH.$mode;

        return $path;
    }
}
