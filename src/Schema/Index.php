<?php

namespace Neoxygen\NeoClient\Schema;

class Index
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
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getProperty() {
        return $this->property;
    }


}