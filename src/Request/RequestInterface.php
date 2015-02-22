<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Request;

interface RequestInterface
{
    public function getMethod();

    public function getUrl();

    public function getBody();

    public function getHeaders();

    public function getOptions();
}
