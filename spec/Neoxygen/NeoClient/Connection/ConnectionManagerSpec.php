<?php

namespace spec\Neoxygen\NeoClient\Connection;

use PhpSpec\ObjectBehavior;
use Neoxygen\NeoClient\Connection\Connection;

class ConnectionManagerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\NeoClient\Connection\ConnectionManager');
    }

    public function it_should_have_an_empty_array_of_connections()
    {
        $this->getConnections()->shouldHaveCount(0);
    }

    public function it_should_register_new_connections(Connection $connection)
    {
        $connection->getAlias()->willReturn('default');
        $this->registerConnection($connection);
        $this->getConnections()->shouldHaveCount(1);
    }

    public function it_should_return_a_connection_for_a_specific_alias(Connection $connection)
    {
        $connection->getAlias()->willReturn('default');
        $this->registerConnection($connection);
        $this->getConnection('default')->shouldHaveType('Neoxygen\NeoClient\Connection\Connection');
    }

    public function it_should_throw_error_if_connection_does_not_exist()
    {
        $this->shouldThrow('Neoxygen\NeoClient\Exception\InvalidConnectionException')->duringGetConnection();
    }

    public function it_should_throw_error_for_default_connection_when_none_configured()
    {
        $this->shouldThrow('Neoxygen\NeoClient\Exception\InvalidConnectionException')->duringGetDefaultConnection();
    }

    public function it_should_throw_error_when_defining_default_con_that_does_not_exist()
    {
        $this->shouldThrow('Neoxygen\NeoClient\Exception\InvalidConnectionException')->duringSetDefaultConnection('default');
    }

    public function it_should_set_the_default_connection_when_it_exist(Connection $connection)
    {
        $connection->getAlias()->willReturn('default');
        $this->registerConnection($connection);
        $this->setDefaultConnection('default');
        $this->getDefaultConnection()->shouldReturn($connection);
    }

    public function it_should_return_the_first_connection_when_none_default_is_configured()
    {
        $connection = $this->getIConnection();
        $connection2 = $this->getIConnection();
        $this->registerConnection($connection);
        $this->registerConnection($connection2);
        $this->getConnections()->shouldHaveCount(2);

        $this->getDefaultConnection()->shouldReturn($connection);
    }

    public function it_should_return_the_default_Connection_2()
    {
            $connection = $this->getIConnection();
            $connection2 = $this->getIConnection();
            $this->registerConnection($connection);
            $this->registerConnection($connection2);
            $this->setDefaultConnection($connection2->getAlias());
            $this->getConnections()->shouldHaveCount(2);

            $this->getDefaultConnection()->shouldReturn($connection2);
    }

    public function it_should_return_bool_for_has_connection_alias(Connection $connection)
    {
        $this->hasConnection('default')->shouldReturn(false);
        $connection->getAlias()->willReturn('default');
        $this->registerConnection($connection);
        $this->hasConnection('default')->shouldReturn(true);

    }

    public function it_should_return_default_connection_if_not_connection_is_defined(Connection $connection)
    {
        $connection->getAlias()->willReturn('default');
        $this->registerConnection($connection);
        $this->getConnection()->shouldHaveType('Neoxygen\NeoClient\Connection\Connection');
    }

    private function getIConnection()
    {
        $alias = sha1(microtime(true).uniqid());
        $con = new Connection($alias);

        return $con;

    }
}
