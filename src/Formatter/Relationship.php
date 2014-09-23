<?php

namespace Neoxygen\NeoClient\Formatter;

use Neoxygen\NeoClient\Formatter\Node;

class Relationship
{
    protected $id;

    protected $type;

    protected $startNode;

    protected $endNode;

    protected $properties;

    public function __construct($id, $type, Node $startNode, Node $endNode, array $properties = array())
    {
        $this->id = $id;
        $this->type = strtoupper($type);
        $this->startNode = $startNode;
        $this->endNode = $endNode;
        $this->properties = $properties;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getStartNode()
    {
        return $this->startNode;
    }

    public function getEndNode()
    {
        return $this->endNode;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function hasProperty($property)
    {
        return array_key_exists($property, $this->properties);
    }

    public function hasProperties()
    {
        return !empty($this->properties);
    }

}