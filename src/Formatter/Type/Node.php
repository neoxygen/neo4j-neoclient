<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Formatter\Type;

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

    /**
     * @param $key
     * @return mixed
     */
    public function value($key)
    {
        return $this->properties[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasValue($key)
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * @return array
     */
    public function values()
    {
        return $this->properties;
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys($this->properties);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function containsKey($key)
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->value($key);
    }

    public function asArray()
    {
        return $this->properties;
    }


}