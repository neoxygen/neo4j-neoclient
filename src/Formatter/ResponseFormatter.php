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
use GraphAware\Common\Result\ResultCollection;
use GraphAware\Neo4j\Client\Exception\Neo4jException;

class ResponseFormatter
{
    /**
     * Formats the Neo4j Response.
     *
     * @param array $response
     * @param \GraphAware\Common\Cypher\Statement[] $statements
     *
     * @return \GraphAware\Common\Result\ResultCollection
     */
    public function format(array $response, array $statements)
    {
        if (isset($response['errors'][0])) {
            $e = new Neo4jException($response['errors'][0]['message']);
            $e->setNeo4jStatusCode($response['errors'][0]['code']);

            throw $e;
        }
        $results = new ResultCollection();
        foreach ($response['results'] as $k => $result) {
            $resultO = new Result($statements[$k]);
            $resultO->setFields($result['columns']);
            foreach ($result['data'] as $data) {
                $resultO->pushRecord($data['rest']);
            }
            if (array_key_exists('stats', $result)) {
                $resultO->setStats($result['stats']);
            }
            $results->add($resultO);
        }

        return $results;
    }
}
