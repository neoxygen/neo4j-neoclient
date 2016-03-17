<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) Graph Aware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Formatter;

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
     * @return mixed|\GraphAware\Neo4j\Client\Formatter\Node|\GraphAware\Neo4j\Client\Formatter\Relationship
     */
    public function value($key)
    {
        return $this->values[$this->keyToIndexMap[$key]];
    }

    /**
     * Returns the Node for value <code>$key</code>. Ease IDE integration
     *
     * @param $key
     * @return \GraphAware\Neo4j\Client\Formatter\Node
     *
     * @throws \InvalidArgumentException When the value is not null or instance of Node
     */
    public function nodeValue($key)
    {
        if (!$this->hasValue('n') || !$this->get($key) instanceof NodeInterface) {
            throw new \InvalidArgumentException(sprintf('value for %s is not of type %s', $key, 'NODE'));
        }

        return $this->value($key);
    }

    /**
     * @param $key
     * @return \GraphAware\Neo4j\Client\Formatter\Relationship
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

    public function hasValue($key)
    {
        return array_key_exists($key, $this->keyToIndexMap);
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
     * @return \GraphAware\Neo4j\Client\Formatter\RecordView
     */
    public function record()
    {
        return clone($this);
    }

    /**
     * @param string $key
     *
     * @return \GraphAware\Neo4j\Client\Formatter\Node|\GraphAware\Neo4j\Client\Formatter\Relationship|mixed
     */
    public function get($key)
    {
        return $this->value($key);
    }

    /**
     * @param int $index
     *
     * @return mixed
     */
    public function getByIndex($index)
    {
        return $this->valueByIndex($index);
    }


}