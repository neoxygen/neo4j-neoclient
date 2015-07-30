<?php

namespace Neoxygen\NeoClient\Tests\Helper;

use Neoxygen\NeoClient\Schema\Index;

class SchemaContainsIndexConstraint extends \PHPUnit_Framework_Constraint
{
    /**
     * @var array
     */
    protected $schemaContainer;

    public function __construct(array $schemaContainer)
    {
        $this->schemaContainer = $schemaContainer;
    }

    public function matches($other)
    {
        return $this->checkIndexIsPresent($other);
    }

    public function toString()
    {
        return 'Index is present in schema';
    }

    public function checkIndexIsPresent($index)
    {
        foreach ($this->schemaContainer as $aindex) {
            if ($aindex->getLabel() === $index->getLabel() && $aindex->getProperty() === $index->getProperty()) {
                return true;
            }
        }

        return false;
    }

    public function failureDescription($other)
    {
        return sprintf('a schema index with label "%s" and property "%s" is found.',
          $other->getLabel(),
          $other->getProperty()
        );
    }
}