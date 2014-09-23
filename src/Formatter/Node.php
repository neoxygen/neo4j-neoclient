<?php

namespace Neoxygen\NeoClient\Formatter;

use Neoxygen\NeoClient\Formatter\Relationship;

class Node
{
    protected $id;

    protected $labels;

    protected $properties;

    protected $inboundRelationships;

    protected $outboundRelationships;

    public function __construct($id, array $labels = array(), array $properties = array())
    {
        $this->id = $id;
        $this->labels = $labels;
        $this->properties = $properties;
        $this->inboundRelationships = array();
        $this->outboundRelationships = array();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function hasLabel($label = null)
    {
        if (null !== $label) {
            foreach ($this->getLabels() as $k => $v){
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

    public function getProperties()
    {
        return $this->properties;
    }

    public function getProperty($name)
    {
        if ($this->properties[$name]) {
            return $this->properties[$name];
        }

        return null;
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

    public function getRelationships()
    {
        $relationships = array_merge($this->inboundRelationships, $this->outboundRelationships);

        return $relationships;
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

    public function getRelationshipsByType($type)
    {
        $collection = array();
        foreach ($this->getRelationships() as $relationship) {
            if ($relationship->getType() === $type) {
                $collection[] = $relationship;
            }
        }

        return $collection;
    }
}