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
        $this->assertEquals('test', $cm->getConnection()->getAlias());
        $cm->setDefaultConnection('test');
        $this->assertEquals('http', $cm->getConnection('test')->getScheme());
        $this->assertEquals('http', $cm->getConnection(null)->getScheme());
        $this->assertEquals('test', $cm->getDefaultConnection()->getAlias());


        $this->assertTrue($cm->hasConnection('test'));
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