<?php

namespace Neoxygen\NeoClient\Tests\Connection;

use Neoxygen\NeoClient\Connection\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testClassInstance()
    {
        $con = new Connection('default', 'http', 'localhost', 7474);

        $this->assertFalse($con->isAuth());

        $con->setAuthUser('demo');
        $this->assertEquals('demo', $con->getAuthUser());

        $con->setAuthMode();
        $this->assertTrue($con->isAuth());

        $con->setAuthPassword('secret');
        $this->assertEquals('secret', $con->getAuthPassword());
    }
}