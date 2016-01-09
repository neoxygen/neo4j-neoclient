<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Formatter;

use GraphAware\Common\Cypher\Statement;

class ResponseFormatter implements ResponseFormatterInterface
{
    /**
     * Returns the Neo4j API ResultDataContent to be used during Cypher queries.
     *
     * @return array
     */
    public static function getDefaultResultDataContents()
    {
        return array('rest');
    }

    /**
     * Formats the Neo4j Response.
     *
     * @param $response
     *
     * @return \Neoxygen\NeoClient\Formatter\Result[]
     */
    public function format($response)
    {
        $results = [];
        foreach ($response['results'] as $result) {
            $resultO = new Result(Statement::create(""));
            $resultO->setFields($result['columns']);
            foreach ($result['data'] as $data) {
                $resultO->pushRecord($data['rest']);
            }
            $results[] = $resultO;
        }

        return $results;
    }
}
