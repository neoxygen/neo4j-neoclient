<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Exception;

abstract class Neo4jException extends \Exception implements Neo4jExceptionInterface
{
    /**
     * @return string
     */
    public function effect()
    {
        $classification = $this->classification();

        switch ($classification) {
            case 'ClientError':
                return Neo4jExceptionInterface::EFFECT_ROLLBACK;
            case 'ClientNotification':
                return Neo4jExceptionInterface::EFFECT_NONE;
            case 'DatabaseError':
                return Neo4jExceptionInterface::EFFECT_ROLLBACK;
            case 'TransientError':
                return Neo4jExceptionInterface::EFFECT_ROLLBACK;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid classification "%s" in "%s"', $classification, $this->getMessage()));
        }
    }

    /**
     * @return string
     */
    public function classification()
    {
        $parts = explode('.', $this->getMessage());
        if (!isset($parts[2])) {
            throw new \InvalidArgumentException(sprintf('Could not parse exception message "%"', $this->getMessage()));
        }

        return $parts[2];
    }
}