<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Extension;

use Symfony\Component\Yaml\Yaml;

class NeoClientAuthExtension implements NeoClientExtensionInterface
{
    public static function getAvailableCommands()
    {
        return Yaml::parse(file_get_contents(__DIR__.'/../Resources/extensions/auth_commands.yml'));
    }
}
