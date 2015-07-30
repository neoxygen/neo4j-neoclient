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

class Relationship
{
    /**
     * @var
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Node
     */
    protected $startNode;

    /**
     * @var Node
     */
    protected $endNode;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @param $id
     * @param $type
     * @param Node  $startNode
     * @param Node  $endNode
     * @param array $properties
     */
    public function __construct($id, $type, Node $startNode, Node $endNode, array $properties = array())
    {
        $this->id = $id;
        $this->type = strtoupper($type);
        $this->startNode = $startNode;
        $this->endNode = $endNode;
        $this->properties = $properties;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Node
     */
    public function getStartNode()
    {
        return $this->startNode;
    }

    /**
     * @return Node
     */
    public function getEndNode()
    {
        return $this->endNode;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getProperty($name)
    {
        $value = isset($this->properties[$name]) ? $this->properties[$name] : null;

        return $value;
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function hasProperty($property)
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * @return bool
     */
    public function hasProperties()
    {
        return !empty($this->properties);
    }

    /**
     * @param Node $node
     *
     * @return Node
     */
    public function getOtherNode(Node $node)
    {
        if ($node === $this->getStartNode()) {
            return $this->getEndNode();
        }

        return $this->getStartNode();
    }
}
