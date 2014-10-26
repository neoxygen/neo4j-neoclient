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

class Result
{
    protected $nodes;

    protected $relationships;

    protected $errors;

    public function __construct()
    {
        $this->nodes = array();
        $this->relationships = array();
    }

    public function addNode(Node $node)
    {
        $this->nodes[$node->getId()] = $node;
    }

    public function addRelationship(Relationship $relationship)
    {
        $this->relationships[$relationship->getId()] = $relationship;
    }

    public function getNodes($label = null, $labelizedKeys = false)
    {
        if (null !== $label){

            return $this->getNodesByLabel($label, $labelizedKeys);
        }
        return $this->nodes;
    }

    public function getNode($id)
    {
        if ($this->nodes[$id]) {
            return $this->nodes[$id];
        }

        return null;
    }

    /**
     * Returns a single node from the nodes collection
     * To use when you do cypher queries returning only one node
     *
     * @return mixed|null
     */
    public function getSingleNode($label = null)
    {
        $nodes = (null === $label) ? $this->getNodes() : $this->getNodesByLabel($label);
        $single = current($nodes);

        return $single;
    }

    /**
     * Returns a single node for a given label
     *
     * @param  string    $label The label to match for
     * @return Node|null The Node or null if not node found matching the label
     */
    public function getSingleNodeByLabel($label)
    {
        foreach ($this->nodes as $node) {
            if ($node->hasLabel($label)) {
                return $node;
            }
        }

        return null;
    }

    public function getNodesByLabel($name, $labelizedKeys = false)
    {
        $collection = array();
        foreach ($this->getNodes() as $node) {
            if ($node->hasLabel($name)) {
                if ($labelizedKeys){
                    $collection[$name] = $node;
                } else {
                    $collection[] = $node;
                }

            }
        }

        return $collection;
    }

    public function getNodesByLabels(array $labels = array(), $labelizedKeys = false)
    {
        $nodes = [];
        foreach ($labels as $label) {
            $lnodes = $this->getNodesByLabel($label);
            foreach ($lnodes as $node){
                if ($labelizedKeys){
                    $nodes[$label] = $node;
                } else {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    public function getRelationships()
    {
        return $this->relationships;
    }

    public function getRelationship($id)
    {
        if ($this->relationships[$id]) {
            return $this->relationships[$id];
        }

        return null;
    }

    public function getNodesCount()
    {
        return count($this->nodes);
    }

    public function getRelationshipsCount()
    {
        return count($this->relationships);
    }
}
