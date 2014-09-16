<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\Client;
use Neoxygen\NeoClient\ServiceContainer;

class NeoClientTestCase extends \PHPUnit_Framework_TestCase
{
    public function getDefaultConfig()
    {
        return __DIR__.'/../../../database_settings.yml';
    }

    public function build()
    {
        $client = new Client();
        $client->loadConfigurationFile($this->getDefaultConfig());
        $client->build();

        return $client;
    }
}