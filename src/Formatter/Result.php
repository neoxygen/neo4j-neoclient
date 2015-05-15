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

class Result
{
    protected $nodes;

    protected $relationships;

    protected $errors;

    protected $identifiers = [];

    protected $tableFormat;

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
        if (null !== $label) {
            if (is_array($label)) {
                $nodes = [];
                foreach ($label as $lbl) {
                    $nodes[$lbl] = $this->getNodesByLabel($lbl);
                }

                return $nodes;
            }

            return $this->getNodesByLabel($label, $labelizedKeys);
        }

        return $this->nodes;
    }

    public function getNodeById($id)
    {
        if ($this->nodes[$id]) {
            return $this->nodes[$id];
        }

        return;
    }

    /**
     * Returns a single node from the nodes collection
     * To use when you do cypher queries returning only one node.
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
     * Returns a single node for a given label.
     *
     * @param string $label The label to match for
     *
     * @return Node|null The Node or null if not node found matching the label
     */
    public function getSingleNodeByLabel($label)
    {
        foreach ($this->nodes as $node) {
            if ($node->hasLabel($label)) {
                return $node;
            }
        }

        return;
    }

    public function getNodesByLabel($name, $labelizedKeys = false)
    {
        $collection = array();
        foreach ($this->getNodes() as $node) {
            if ($node->hasLabel($name)) {
                if ($labelizedKeys) {
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
            foreach ($lnodes as $node) {
                if ($labelizedKeys) {
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

        return;
    }

    public function getNodesCount()
    {
        return count($this->nodes);
    }

    public function getRelationshipsCount()
    {
        return count($this->relationships);
    }

    public function addNodeToIdentifier($nodeId, $identifier)
    {
        $this->identifiers[$identifier][] = $this->getNodeById($nodeId);
    }

    public function addRelationshipToIdentifier($relationshipId, $identifier)
    {
        $this->identifiers[$identifier][] = $this->getRelationship($relationshipId);
    }

    public function addRowToIdentifier($value, $identifier)
    {
        $this->identifiers[$identifier][] = $value;
    }

    public function get($identifier, $default = null, $singleAsArray = false)
    {
        if (!array_key_exists($identifier, $this->identifiers)) {
            return $default;
        }

        if (is_array($this->identifiers[$identifier]) && 1 === count($this->identifiers[$identifier]) && $singleAsArray === false) {

            return array_values($this->identifiers[$identifier])[0];
        }

        return $this->identifiers[$identifier];
    }

    public function getSingle($identifier, $default = null)
    {
        $get = $this->get($identifier, $default);
        if (is_array($get)) {

            return array_values($this->identifiers[$identifier])[0];
        }

        return $get;
    }

    public function getIdentifiers()
    {
        return array_keys($this->identifiers);
    }

    public function getAllByIdentifier()
    {
        return $this->identifiers;
    }

    public function hasIdentifier($i)
    {
        return array_key_exists($i, $this->identifiers);
    }

    public function addIdentifierValue($k, $v) {
        if (array_key_exists($k, $this->identifiers)) {
            return $this->addRowToIdentifier($k, $v);
        }

        return $this->identifiers[$k] = $v;
    }

    public function setTableFormat(array $table)
    {
        $this->tableFormat = $table;
    }

    public function getTableFormat()
    {
        return $this->tableFormat;
    }

    public function toGoogleDataTable()
    {
        $dt = [];
        foreach ($this->getTableFormat()[0] as $k => $v) {
            $col = [
                'id' => strtolower($k),
                'label' => ucfirst($k),
                'type' => is_int($k) ? 'number' : 'string'
            ];
            $dt['cols'][] = $col;
        }
        foreach ($this->getTableFormat() as $k => $v) {
            $row = [];
            foreach ($v as $key => $value) {
                $row[] = ['v' => $value];
            }
            $dt['rows'][] = ['c' => $row];
        }

        return json_encode($dt, JSON_PRETTY_PRINT);
    }
}
