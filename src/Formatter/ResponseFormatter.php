<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Neoxygen\NeoClient\Formatter;

class ResponseFormatter implements ResponseFormatterInterface
{

    protected $nodesMap = [];

    protected $relationshipsMap = [];

    protected $errors = [];

    protected $nodesByLabel = [];

    protected $relsByType = [];

    protected $result;

    public static function getDefaultResultDataContents()
    {
        return array('row', 'graph');
    }

    public function __construct()
    {
        $this->result = new Result();
    }

    public function hasErrors()
    {
        return null !== $this->errors;
    }

    public function format($response)
    {
        $responseObject = new Response();
        $responseObject->setRawResponse($response);

        if ($responseObject->containsResults()) {
            $resultSet = $response;

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

            $this->prepareResultSet();
            $this->prepareNodesByLabels();
            $this->prepareRelationshipsByType();

            $responseObject->setResult($this->result);
        }

        return $responseObject;
    }

    public function getNodes()
    {
        return $this->nodesMap;
    }

    public function getRelationships()
    {
        return $this->relationshipsMap;
    }

    public function getRelationshipsByType($type)
    {
        if ($this->relsByType[$type]) {
            return $this->relsByType[$type];
        }

        return null;
    }

    public function getNodesByLabel($label)
    {
        if ($this->nodesByLabel[$label]) {
            return $this->nodesByLabel[$label];
        }
    }

    public function hasNodes()
    {
        return !empty($this->nodesMap);
    }

    public function hasRelationships()
    {
        return !empty($this->relationshipsMap);
    }

    public function getGraph()
    {
        return array(
            'nodes' => $this->nodesMap,
            'relationships' => $this->relationshipsMap
        );
    }

    private function prepareNodesByLabels()
    {
        foreach ($this->nodesMap as $node) {
            foreach ($node['labels'] as $label) {
                $this->nodesByLabel[$label][] = $node;
            }
        }
    }

    private function prepareRelationshipsByType()
    {
        foreach ($this->relationshipsMap as $rel) {
            $this->relsByType[$rel['type']][] = $rel;
        }
    }

    private function prepareResultSet()
    {
        foreach ($this->nodesMap as $node) {
            $n = new Node($node['id'], $node['labels'], $node['properties']);
            $this->result->addNode($n);
        }

        foreach ($this->relationshipsMap as $relationship) {
            $startNode = $this->result->getNode($relationship['startNode']);
            $endNode = $this->result->getNode($relationship['endNode']);
            $r = new Relationship($relationship['id'], $relationship['type'], $startNode, $endNode, $relationship['properties']);
            $this->result->addRelationship($r);
            $startNode->addOutboundRelationship($r);
            $endNode->addInboundRelationship($r);
        }
    }

}
