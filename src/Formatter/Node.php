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

class Node
{
    /**
     * @var int the node internal ID
     */
    protected $id;

    /**
     * @var array Collection of the node labels
     */
    protected $labels;

    /**
     * @var array The properties of the node
     */
    protected $properties;

    /**
     * @var array[Neoxygen\NeoClient\Formatter\Relationship] The collection of inbound relationships
     */
    protected $inboundRelationships;

    /**
     * @var array[Neoxygen\NeoClient\Formatter\Relationship] The collection of outbound relationships
     */
    protected $outboundRelationships;

    /**
     * @param $id
     * @param array $labels
     * @param array $properties
     */
    public function __construct($id, array $labels = array(), array $properties = array())
    {
        $this->id = (int) $id;
        $this->labels = $labels;
        $this->properties = $properties;
        $this->inboundRelationships = array();
        $this->outboundRelationships = array();
    }

    /**
     * Returns the node internal id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array collection of node's labels
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param string|null $label The label to check for
     *
     * @return bool True if the label is matched or if no label is given if the node has minimum 1 label, false otherwise
     */
    public function hasLabel($label = null)
    {
        if (null !== $label) {
            foreach ($this->getLabels() as $k => $v) {
                if ($v == $label) {
                    return true;
                }
            }
        }

        if (null === $label && !empty($this->labels)) {
            return true;
        }

        return false;
    }

    /**
     * Used when only one label is expected.
     *
     * @return string|null the label of the node
     */
    public function getLabel()
    {
        if (empty($this->labels)) {
            return;
        }
        reset($this->labels);

        return current($this->labels);
    }

    public function getProperties(array $props = array())
    {
        if (empty($props)) {
            return $this->properties;
        }

        $properties = [];
        foreach ($props as $key) {
            $properties[$key] = isset($this->properties[$key]) ? $this->properties[$key] : null;
        }

        return $properties;
    }

    public function getProperty($name)
    {
        if ($this->properties[$name]) {
            return $this->properties[$name];
        }

        return;
    }

    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    public function addInboundRelationship(Relationship $relationship)
    {
        $this->inboundRelationships[$relationship->getId()] = $relationship;
    }

    public function addOutboundRelationship(Relationship $relationship)
    {
        $this->outboundRelationships[$relationship->getId()] = $relationship;
    }

    public function getInboundRelationships()
    {
        return $this->inboundRelationships;
    }

    public function getOutboundRelationships()
    {
        return $this->outboundRelationships;
    }

    public function getRelationships($type = null, $direction = null)
    {
        if (null === $direction) {
            $relationships = array_merge($this->inboundRelationships, $this->outboundRelationships);
        } else {
            $dir = strtoupper($direction);
            if (!in_array($direction, array('IN', 'OUT'))) {
                throw new \InvalidArgumentException(sprintf('The direction "%s" is not valid', $direction));
            }
            $relationships = ('IN' === $dir) ? $this->getInboundRelationships() : $this->getOutboundRelationships();
        }

        if (null === $type) {
            return $relationships;
        }

        $collection = array();
        foreach ($relationships as $rel) {
            if ($rel->getType() === $type) {
                $collection[] = $rel;
            }
        }

        return $collection;
    }

    public function getSingleRelationship($type = null, $direction = null)
    {
        $relationships = $this->getRelationships($type, $direction);
        reset($relationships);

        return current($relationships);
    }

    public function hasRelationships()
    {
        if (!empty($this->inboundRelationships) || !empty($this->outboundRelationships)) {
            return true;
        }

        return false;
    }

    public function getRelationshipsCount()
    {
        return count($this->getRelationships());
    }
}
