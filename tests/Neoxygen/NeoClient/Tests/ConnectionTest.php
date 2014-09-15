<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\Exception\InvalidConnectionException;

class ConnectionTest extends NeoClientTestCase
{
    public function testGetConnection()
    {
        $sc = $this->build();
        $cm = $sc->getConnectionManager();

        $this->assertCount(1, $cm->getConnections());
        $this->assertEquals('default', $cm->getConnection()->getAlias());
        $cm->setDefaultConnection('default');
        $this->assertEquals('http', $cm->getConnection('default')->getScheme());
        $this->assertEquals('http', $cm->getConnection(null)->getScheme());
        $this->assertEquals('default', $cm->getDefaultConnection()->getAlias());


        $this->assertTrue($cm->hasConnection('default'));
    }

    /**
     * @expectedException Neoxygen\NeoClient\Exception\InvalidConnectionException
     */
    public function testExceptionsAreThrownWhenBadConnection()
    {
        $sc = $this->build();
        $cm = $sc->getConnectionManager();

        $cm->getConnection('badAlias');
        $cm->setDefaultConnection('badAlias');
    }

    /**
     * @expectedException Neoxygen\NeoClient\Exception\InvalidConnectionException
     */
    public function testExceptionsAreThrownWhenBadDefaultConnection()
    {
        $sc = $this->build();
        $cm = $sc->getConnectionManager();

        $cm->setDefaultConnection('badAlias');
    }
}