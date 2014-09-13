<?php

namespace spec\Neoxygen\NeoClient\Connection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(Argument::any());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\NeoClient\Connection\Connection');
    }

    public function it_should_have_an_alias_on_construct()
    {
        $this->getAlias()->shouldNotBeNull();
    }

    public function it_should_have_a_default_http_scheme()
    {
        $this->getScheme()->shouldReturn('http');
    }

    public function it_should_have_a_default_host()
    {
        $this->getHost()->shouldNotBeNull();
    }

    public function it_should_have_a_port_by_default()
    {
        $this->getPort()->shouldNotBeNull();
    }

    public function it_should_return_the_base_url()
    {
        $this->getBaseUrl()->shouldReturn('http://localhost:7474');
    }

    public function its_auth_mode_should_not_be_set_by_default()
    {
        $this->shouldNotBeAuth();
    }

    public function its_auth_mode_should_be_switchable()
    {
        $this->setAuthMode();
        $this->shouldBeAuth();
    }

    public function it_should_not_have_a_auth_user_by_default()
    {
        $this->getAuthUser()->shouldBeNull();
    }

    public function its_user_should_be_mutable()
    {
        $this->setAuthUser(Argument::any());
        $this->getAuthUser()->shouldNotBeNull();
    }

    public function it_should_not_have_a_auth_password()
    {
        $this->getAuthPassword()->shouldBeNull();
    }

    public function its_auth_password_should_be_mutable()
    {
        $this->setAuthPassword(Argument::any());
        $this->getAuthPassword()->shouldNotBeNull();
    }
}
