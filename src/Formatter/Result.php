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
    /** @var Node[] */
    protected $nodes;

    /** @var Relationship[] */
    protected $relationships;

    protected $errors;

    /** @var array */
    protected $identifiers = [];

    /** @var  array */
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

    /**
     * Returns all nodes if called without arguments. Returns all nodes with
     * the given labels if called with an array of labels. Otherwise, acts
     * identically as {@link Result::getNodesByLabels()}.
     *
     * @param string|string[]|null $label
     * @param bool                 $labelizedKeys
     *
     * @return Node[]
     */
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

    /**
     * Returns a single node by its Neo4j node ID number, or null if the node
     * is not present in the result.
     *
     * @param int $id Neo4j node ID.
     *
     * @return Node|null
     */
    public function getNodeById($id)
    {
        if (!isset($this->nodes[$id])) {
            return;
        }

        return $this->nodes[$id];
    }

    /**
     * Returns a single node from the nodes collection
     * Use when you do cypher queries returning only one node.
     *
     * @param string|null $label Return a node for this label only.
     *
     * @return Node|null
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

    /**
     * Returns all nodes with the given label.
     *
     * @param string $name
     * @param bool   $labelizedKeys When true, the results are indexed by node
     *                              label. Assumes only one node per label.
     *
     * @return Node[]
     */
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

    /**
     * Returns all nodes with the given labels.
     *
     * @param array $labels
     * @param bool  $labelizedKeys When true, the results are indexed by node
     *                             label. Assumes one node per label.
     *
     * @return Node[]
     */
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

    /**
     * @return Relationship[]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Returns the relationship by its Neo4j ID.
     *
     * @param int $id The id of the relationship.
     *
     * @return Relationship|null
     */
    public function getRelationship($id)
    {
        if (!isset($this->relationships[$id])) {
            return;
        }

        return $this->relationships[$id];
    }

    /**
     * @return int Number of nodes in the result.
     */
    public function getNodesCount()
    {
        return count($this->nodes);
    }

    /**
     * @return int Number of relationships in the result.
     */
    public function getRelationshipsCount()
    {
        return count($this->relationships);
    }

    public function addNodeToIdentifier($nodeId, $identifier)
    {
        if (isset($this->identifiers[$identifier])) {
            foreach ($this->identifiers[$identifier] as $node) {
                if (null === $node || $node->getId() === $nodeId) {
                    return;
                }
            }
        }
        $this->identifiers[$identifier][] = $this->getNodeById($nodeId);
    }

    public function addRelationshipToIdentifier($relationshipId, $identifier)
    {
        if (isset($this->identifiers[$identifier])) {
            foreach ($this->identifiers[$identifier] as $rel) {
                if ($rel instanceof Relationship && $rel->getId() === $relationshipId) {
                    return;
                }
            }
        }
        $this->identifiers[$identifier][] = $this->getRelationship($relationshipId);
    }

    public function addRowToIdentifier($value, $identifier)
    {
        $this->identifiers[$identifier][] = $value;
    }

    /**
     * Returns the item or items bound to the given identifier, or $default
     * if no items are bound.
     *
     * @param string $identifier
     * @param mixed  $default       A value to return if the identifier is not bound.
     * @param bool   $singleAsArray When true, always returns a single value as
     *                              an array.
     *
     * @return mixed
     */
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

    /**
     * Returns a single item bound to the given identifier, or the default if
     * the identifier is not bound.
     *
     * @param string $identifier
     * @param mixed  $default    A value to return if the identifier is not bound.
     *
     * @return mixed
     */
    public function getSingle($identifier, $default = null)
    {
        $get = $this->get($identifier, $default);
        if (is_array($get)) {
            return array_values($this->identifiers[$identifier])[0];
        }

        return $get;
    }

    /**
     * @return string[]
     */
    public function getIdentifiers()
    {
        return array_keys($this->identifiers);
    }

    /**
     * @return array
     */
    public function getAllByIdentifier()
    {
        return $this->identifiers;
    }

    /**
     * @param string $i Query identifier to check.
     *
     * @return bool
     */
    public function hasIdentifier($i)
    {
        return array_key_exists($i, $this->identifiers);
    }

    public function addIdentifierValue($k, $v)
    {
        if (array_key_exists($k, $this->identifiers)) {
            return $this->addRowToIdentifier($k, $v);
        }

        return $this->identifiers[$k] = $v;
    }

    public function setTableFormat(array $table)
    {
        $this->tableFormat = $table;
    }

    /**
     * @return array
     */
    public function getTableFormat()
    {
        return $this->tableFormat;
    }
}
