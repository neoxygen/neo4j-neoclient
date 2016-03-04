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

class Path
{
    /**
     * @var int
     */
    protected $length;

    /**
     * @var \Neoxygen\NeoClient\Formatter\Relationship[]
     */
    protected $relationships = [];

    /**
     * Path constructor.
     * @param int $length
     * @param \Neoxygen\NeoClient\Formatter\Relationship[] $relationships
     */
    public function __construct($length, array $relationships)
    {
        $this->length = $length;
        $this->relationships = $relationships;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return Relationship[]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }
}