<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Schema;

class UniqueConstraint
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $property;

    /**
     * @param string $label
     * @param string $property
     */
    public function __construct($label, $property)
    {
        $this->label = (string) $label;
        $this->property = (string) $property;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }
}
