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

class NeoClientChangeFeedExtension implements NeoClientExtensionInterface
{
    public static function getAvailableCommands()
    {
        return array(
            'neo.changefeed' => array(
                'class' => 'Neoxygen\NeoClient\Command\ChangeFeed\ChangeFeedCommand',
            ),
        );
    }
}
