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

class ResponseFormatter implements ResponseFormatterInterface
{
    /**
     * @var array
     */
    protected $nodesMap = [];

    /**
     * @var array
     */
    protected $relationshipsMap = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $nodesByLabel = [];

    /**
     * @var array
     */
    protected $relsByType = [];

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var bool
     */
    protected $isNew = true;

    /**
     * Returns the Neo4j API ResultDataContent to be used during Cypher queries.
     *
     * @return array
     */
    public static function getDefaultResultDataContents()
    {
        return array('row', 'graph', 'rest');
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->result = new Result();
    }

    /**
     * Returns whether or not the Neo4j response contains errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return null !== $this->errors;
    }

    /**
     * Formats the Neo4j Response.
     *
     * @param $response
     *
     * @return Response
     */
    public function format($response)
    {
        $this->isNew = false;
        $responseObject = new Response();
        $responseObject->setRawResponse($response);

        if ($responseObject->containsResults()) {
            $this->extractResults($response);
            $this->prepareResultSet();
            $this->prepareNodesByLabels();
            $this->prepareRelationshipsByType();
            $this->processIdentification($response);
        }

        if ($responseObject->containsRows()) {
            $rows = $this->formatRows($response);
            $responseObject->setRows($rows);

            if ($responseObject->containsResults()) {
                foreach ($responseObject->geRows() as $k => $v) {
                    if (!$this->result->hasIdentifier($k)) {
                        $this->result->addIdentifierValue($k, $v);
                    }
                }
            }
        }

        if (is_array($responseObject->geRows())) {
            $this->processTableFormat($responseObject->geRows());
        }
        $responseObject->setResult($this->result);

        $this->reset();

        return $responseObject;
    }

    /**
     * Returns the nodes from the Response array.
     *
     * @return array
     */
    public function getNodes()
    {
        return $this->nodesMap;
    }

    /**
     * @return array
     */
    public function getRelationships()
    {
        return $this->relationshipsMap;
    }

    /**
     * @param $type
     */
    public function getRelationshipsByType($type)
    {
        if ($this->relsByType[$type]) {
            return $this->relsByType[$type];
        }

        return;
    }

    /**
     * @param $label
     *
     * @return mixed
     */
    public function getNodesByLabel($label)
    {
        if ($this->nodesByLabel[$label]) {
            return $this->nodesByLabel[$label];
        }
    }

    /**
     * @return bool
     */
    public function hasNodes()
    {
        return !empty($this->nodesMap);
    }

    /**
     * @return bool
     */
    public function hasRelationships()
    {
        return !empty($this->relationshipsMap);
    }

    /**
     * @return array
     */
    public function getGraph()
    {
        return array(
            'nodes' => $this->nodesMap,
            'relationships' => $this->relationshipsMap,
        );
    }

    /**
     * Extracts the results from the Neo4j Response.
     *
     * @param $resultSet
     */
    private function extractResults($resultSet)
    {
        foreach ($resultSet['results'] as $result) {
            foreach ($result['data'] as $data) {
                if (isset($data['graph'])) {
                    foreach ($data['graph']['nodes'] as $node) {
                        $this->nodesMap[$node['id']] = $node;
                    }
                    foreach ($data['graph']['relationships'] as $rel) {
                        $this->relationshipsMap[$rel['id']] = $rel;
                    }
                }
            }
        }
    }

    /**
     *
     */
    private function prepareNodesByLabels()
    {
        foreach ($this->nodesMap as $node) {
            foreach ($node['labels'] as $label) {
                $this->nodesByLabel[$label][] = $node;
            }
        }
    }

    /**
     *
     */
    private function prepareRelationshipsByType()
    {
        foreach ($this->relationshipsMap as $rel) {
            $this->relsByType[$rel['type']][] = $rel;
        }
    }

    /**
     *
     */
    private function prepareResultSet()
    {
        foreach ($this->nodesMap as $node) {
            $n = new Node($node['id'], $node['labels'], $node['properties']);
            $this->result->addNode($n);
        }

        foreach ($this->relationshipsMap as $relationship) {
            $startNode = $this->result->getNodeById($relationship['startNode']);
            $endNode = $this->result->getNodeById($relationship['endNode']);
            $r = new Relationship($relationship['id'], $relationship['type'], $startNode, $endNode, $relationship['properties']);
            $this->result->addRelationship($r);
            $startNode->addOutboundRelationship($r);
            $endNode->addInboundRelationship($r);
        }
    }

    /**
     * @param $response
     */
    private function processIdentification($response)
    {
        foreach ($response['results'] as $result) {
            $columns = $result['columns'];
            foreach ($result['data'] as $dat) {
                foreach ($dat['rest'] as $idx => $restx) {
                    $this->processRestEltType($restx, $columns, $idx);
                }
            }
        }
    }

    /**
     * @param $elts
     * @param $columns
     * @param $idx
     */
    private function processRestEltType($elts, $columns, $idx)
    {
        if (isset($elts[0]) && is_array($elts[0])) {
            foreach ($elts as $elt) {
                $this->processRestEltType($elt, $columns, $idx);
            }
        } else {
            if (is_array($elts)) {
                if (array_key_exists('labels', $elts)) {
                    $this->result->addNodeToIdentifier($elts['metadata']['id'], $columns[$idx]);
                } elseif (array_key_exists('type', $elts)) {
                    $this->result->addRelationshipToIdentifier($elts['metadata']['id'], $columns[$idx]);
                }
            } else {
                $this->result->addRowToIdentifier($elts, $columns[$idx]);
            }
        }
    }

    /**
     * @param $response
     *
     * @return array
     */
    private function formatRows($response)
    {
        $rows = [];
        foreach ($response['results'] as $result) {
            $columns = $result['columns'];
            $tmpColumns = [];
            foreach ($result['data'] as $dat) {
                $i = 0;
                foreach ($dat['row'] as $row) {
                    $tmpColumns[$i][] = $row;
                    ++$i;
                }
            }
            $y = 0;
            foreach ($columns as $k => $col) {
                if (!empty($tmpColumns)) {
                    $rows[$col] = $tmpColumns[$k];
                    if (is_array($tmpColumns[$k])) {
                        foreach ($tmpColumns[$k] as $i => $el) {
                            if (is_array($el) && isset($el[0])) {
                                $el[0] = 'maybe relationship';
                                if (isset($response['results'][0]['data'][$k])) {
                                    $maybeRel = $response['results'][0]['data'][$k]['rest'][$y];
                                    if (isset($maybeRel['start'])) {
                                        $rows[$col][$i] = $this->getOnlyUsefulEdgeInfoFromRestFormat($maybeRel);
                                    }
                                    if (is_array($maybeRel)) {
                                        $areRels = false;
                                        foreach ($maybeRel as $rel) {
                                            if (isset($rel['start'])) {
                                                $areRels = true;
                                            }
                                        }
                                        if ($areRels) {
                                            $rows[$col][$i] = $this->getUsefulRestEdgeInfoFromCollection($maybeRel);
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
                ++$y;
            }
        }

        return $rows;
    }

    private function getOnlyUsefulEdgeInfoFromRestFormat(array $rel)
    {
        $data = [
            'id' => $rel['metadata']['id'],
            'type' => $rel['metadata']['type'],
            'properties' => $rel['data'],
        ];

        return $data;
    }

    private function getUsefulRestEdgeInfoFromCollection(array $rels)
    {
        $data = [];
        foreach ($rels as $rel) {
            $data[] = $this->getOnlyUsefulEdgeInfoFromRestFormat($rel);
        }

        return $data;
    }

    private function processTableFormat(array $rows = array())
    {
        $table = [];
        foreach ($rows as $k => $values) {
            foreach ($values as $i => $val) {
                $table[$i][$k] = $val;
            }
        }
        $this->result->setTableFormat($table);
    }

    /**
     * Resets the results collections for next Result process.
     */
    public function reset()
    {
        $this->isNew = true;
        $this->nodesMap = array();
        $this->relationshipsMap = array();
        $this->nodesByLabel = array();
        $this->result = new Result();
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }
}
