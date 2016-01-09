<?php

/*
 * This file is part of the GraphAware Bolt package.
 *
 * (c) Graph Aware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Formatter;

use GraphAware\Common\Result\RecordViewInterface;
use GraphAware\Common\Type\NodeInterface;
use GraphAware\Common\Type\RelationshipInterface;

class RecordView implements RecordViewInterface
{
    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    private $keyToIndexMap = [];

    /**
     * @return array
     */
    public function keys()
    {
        return $this->keys;
    }

    /**
     * RecordView constructor.
     * @param array $keys
     * @param array $values
     */
    public function __construct(array $keys, array $values)
    {
        $this->keys = $keys;
        $this->values = $values;
        foreach ($this->keys as $i => $k) {
            $this->keyToIndexMap[$k] = $i;
        }
    }

    /**
     * @return bool
     */
    public function hasValues()
    {
        return !empty($this->values);
    }

    /**
     * @param $key
     * @return mixed|\Neoxygen\NeoClient\Formatter\Node|\Neoxygen\NeoClient\Formatter\Relationship
     */
    public function value($key)
    {
        return $this->values[$this->keyToIndexMap[$key]];
    }

    /**
     * Returns the Node for value <code>$key</code>. Ease IDE integration
     *
     * @param $key
     * @return \Neoxygen\NeoClient\Formatter\Node
     *
     * @throws \InvalidArgumentException When the value is not null or instance of Node
     */
    public function nodeValue($key)
    {
        if ($this->values[$key] !== null && !$this->values[$key] instanceof NodeInterface) {
            throw new \InvalidArgumentException(sprintf('value for %s is not of type %s', $key, 'NODE'));
        }

        return $this->value($key);
    }

    /**
     * @param $key
     * @return \Neoxygen\NeoClient\Formatter\Relationship
     *
     * @throws \InvalidArgumentException When the value is not null or instance of Relationship
     */
    public function relationshipValue($key) {
        if ($this->values[$key] !== null && !$this->values[$key] instanceof RelationshipInterface) {
            throw new \InvalidArgumentException(sprintf('value for %s is not of type %s', $key, 'RELATIONSHIP'));
        }
    }

    public function pathValue($key)
    {
        // TODO: Implement pathValue() method.
    }

    /**
     * @return array
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * @param $index
     * @return mixed
     */
    public function valueByIndex($index)
    {
        return $this->values[$index];
    }

    /**
     * @return \Neoxygen\NeoClient\Formatter\RecordView
     */
    public function record()
    {
        return clone($this);
    }

}