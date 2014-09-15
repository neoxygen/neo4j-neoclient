<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ServiceContainer;

class NeoClientTestCase extends \PHPUnit_Framework_TestCase
{
    public function getDefaultConfig()
    {
        return __DIR__.'/../../../../spec/Neoxygen/NeoClient/default_config.yml';
    }

    public function build()
    {
        $sc = new ServiceContainer();
        $sc->loadConfiguration($this->getDefaultConfig());
        $sc->build();

        return $sc;
    }
}