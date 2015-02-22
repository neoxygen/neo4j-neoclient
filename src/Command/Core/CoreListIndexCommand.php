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
use Neoxygen\NeoClient\Exception\CommandException;

class CoreListIndexCommand extends AbstractCommand
{
    const METHOD = 'GET';

    const PATH = '/db/data/schema/index/';

    private $label;

    public function setArguments($label)
    {
        $this->label = $label;
    }

    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), null, $this->connection);
    }

    private function getPath()
    {
        if (null === $this->label) {
            throw new CommandException('A label must be given to find an index on');
        }

        return self::PATH.$this->label;
    }
}
