<?php

namespace Neoxygen\NeoClient\Formatter\Type;

use GraphAware\Common\Type\NodeInterface;

class Node implements NodeInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var array
     */
    protected $properties = [];

    public function __construct($id, array $labels, array $properties)
    {
        $this->id = $id;
        $this->labels = $labels;
        $this->properties = $properties;
    }

    /**
     * @return int
     */
    public function identity()
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function labels()
    {
        return $this->labels;
    }

    /**
     * @param string $label
     *
     * @return bool
     */
    public function hasLabel($label)
    {
        return in_array($label, $this->labels);
    }

    public function value($key)
    {
        return $this->properties[$key];
    }

    public function values()
    {
        return $this->properties;
    }
}