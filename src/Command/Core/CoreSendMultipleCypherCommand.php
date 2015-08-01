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

class CoreSendMultipleCypherCommand extends AbstractCommand
{
    const METHOD = 'POST';

    const PATH = '/db/data/transaction/commit';

    public $statements;

    public $resultDataContents;

    public $queryMode;

    public function setArguments(array $statements, array $resultDataContents = array(), $queryMode = null)
    {
        $this->statements = $statements;
        $this->resultDataContents = $resultDataContents;
        $this->queryMode = $queryMode;

        return $this;
    }

    public function execute()
    {
        return $this->process(self::METHOD, self::PATH, $this->prepareBody(), $this->connection, null, $this->queryMode);
    }

    public function prepareBody()
    {
        $sts = [];
        foreach ($this->statements as $statement) {
            $statement['resultDataContents'] = $this->resultDataContents;
            if (empty($statement['parameters'])) {
                unset($statement['parameters']);
            }
            $sts[] = $statement;
        }

        $body = array(
            'statements' => $sts,
        );

        return json_encode($body);
    }
}
