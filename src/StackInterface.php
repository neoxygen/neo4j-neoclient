<?php namespace GraphAware\Neo4j\Client;

use GraphAware\Common\Cypher\Statement;

/**
 * Interface StackInterface
 * @package GraphAware\Neo4j\Client
 */
interface StackInterface
{
    /**
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return Stack
     */
    public static function create($tag = null, $connectionAlias = null);

    /**
     * @param string $query
     * @param null|array $parameters
     * @param null|array $tag
     */
    public function push($query, $parameters = null, $tag = null);

    /**
     * @param $query
     * @param array|null $parameters
     * @param array|null $tag
     */
    public function addPreflight($query, $parameters = null, $tag = null);

    /**
     * @return bool
     */
    public function hasPreflights();

    /**
     * @return \GraphAware\Common\Cypher\Statement[]
     */
    public function getPreflights();

    /**
     * @return int
     */
    public function size();

    /**
     * @return Statement[]
     */
    public function statements();

    /**
     * @return null|string
     */
    public function getTag();

    /**
     * @return null|string
     */
    public function getConnectionAlias();
}