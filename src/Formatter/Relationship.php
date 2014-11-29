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

class Relationship
{
    protected $id;

    protected $type;

    protected $startNode;

    protected $endNode;

    protected $properties;

    public function __construct($id, $type, Node &$startNode, Node &$endNode, array $properties = array())
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

    public function getProperty($name)
    {
        $value = isset($this->properties[$name]) ?: null;

        return $value;
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
