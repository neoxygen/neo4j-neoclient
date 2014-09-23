<?php

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

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getNode($id)
    {
        if ($this->nodes[$id]){
            return $this->nodes[$id];
        }

        return null;
    }

    public function getRelationships()
    {
        return $this->relationships;
    }

    public function getRelationship($id)
    {
        if ($this->relationships[$id]){
            return $this->relationships[$id];
        }

        return null;
    }
}