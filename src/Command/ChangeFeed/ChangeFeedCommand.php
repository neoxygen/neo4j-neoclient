<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Command\ChangeFeed;

use Neoxygen\NeoClient\Command\AbstractCommand;

class ChangeFeedCommand extends AbstractCommand
{
    const METHOD = 'GET';

    const PATH = '/graphaware/changefeed/';

    private $uuid;

    private $limit;

    private $moduleId;

    public function execute()
    {
        return $this->httpClient->send(self::METHOD, $this->getPath(), null, $this->connection, $this->getQuery());
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    public function setModuleId($id)
    {
        $this->moduleId = $id;

        return $this;
    }

    private function getQuery()
    {
        if (!$this->uuid && !$this->limit) {
            return;
        }

        $query = array();
        if ($this->uuid) {
            $query['uuid'] = $this->uuid;
        }
        if ($this->limit) {
            $query['limit'] = $this->limit;
        }

        return $query;
    }

    private function getPath()
    {
        if ($this->moduleId) {
            return self::PATH.$this->moduleId.'/';
        }

        return self::PATH;
    }
}
