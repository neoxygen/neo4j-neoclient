<?php

namespace GraphAware\Neo4j\Client\Transaction;

class Statement
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string|null
     */
    protected $tag;

    /**
     * @var bool
     */
    protected $includeStats;

    /**
     * @param string $query
     * @param array $parameters
     * @param string|null $tag
     * @param bool|false $includeStats
     */
    public function __construct($query, array $parameters = [], $tag = null, $includeStats = false)
    {
        $this->query = (string) $query;
        $this->parameters = $parameters;
        $this->includeStats = (bool) $includeStats;
    }

    /**
     * @param string $query
     * @param array $parameters
     * @param string|null $tag
     * @param bool|false $includeStats
     * @return \GraphAware\Neo4j\Client\Transaction\Statement
     */
    public static function create($query, array $parameters = [], $tag = null, $includeStats = false)
    {
        return new self($query, $parameters, $tag, $includeStats);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string|null
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return boolean
     */
    public function hasIncludeStats()
    {
        return $this->includeStats;
    }
}