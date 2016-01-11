<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Formatter;

use GraphAware\Common\Cypher\Statement;

class ResponseFormatter
{
    /**
     * Formats the Neo4j Response.
     *
     * @param array $response
     * @param \GraphAware\Common\Cypher\Statement[] $statements
     *
     * @return \GraphAware\Neo4j\Client\Formatter\Result[]
     */
    public function format(array $response, array $statements)
    {
        $results = [];
        foreach ($response['results'] as $k => $result) {
            $resultO = new Result($statements[$k]);
            $resultO->setFields($result['columns']);
            foreach ($result['data'] as $data) {
                $resultO->pushRecord($data['rest']);
            }
            if (array_key_exists('stats', $result)) {
                $resultO->setStats($result['stats']);
            }
            $results[] = $resultO;
        }

        return $results;
    }
}
